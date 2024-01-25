<?php
header('Content-Type: application/json');

$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);


$fecha = new Datetime();
$timestamp = $fecha->getTimestamp();


if ($data && isset($data->serialNumber) && isset($data->station)) {
    $serialNumber = $data->serialNumber;
    $station = $data->station;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "laboratorio";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        
        $stmt = findUser($conn, $serialNumber);

        if ($stmt->rowCount() > 0) {

            getUserData($stmt);
            
                if(!isUserHavingStation($conn, $code)){

                    if(!isUserAccompanying($conn, $userName)){
                        
                        $stmt = findWorkstation($conn, $station);

                        if($stmt->rowCount() > 0){                                 

                                $stmt = checkAvailability($conn, $station);  

                                if($stmt->rowCount() > 0){                                 
                                    
                                    $stmt = getStationAndRecordInfo($conn, $station);
                                
                                    if ($stmt->rowCount() > 0) {

                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $partner = $row['Acomp'];
                                        $lastRecordID = $row['ID'];
                                        
                                        if ($partner == 'NA') {
                                                
                                            if(updateEntryStation($conn, $lastRecordID, $userName)){

                                                $response = array(
                                                    "status" => "success (SHARE)",
                                                    "message" => "Acceso concedido.",
                                                    "userName" => $userName,
                                                    "userCode" => $code
                                                );
                                            }
                        
                        
                                        } else {
                                            $response = array(
                                                "status" => "busy",
                                                "message" => "Ya hay dos personas usando esta estacion.",
                                                "userName" => $userName,
                                                "userCode" => $code
                                            );
                                        }


                                    }
                                    else {
                                        $response = array(
                                            "status" => "stationNotFound",
                                            "message" => "No se ha encontrado información de esta estacion.",
                                            "userName" => $userName,
                                            "userCode" => $code
                                        );
                                    }
                
                                }
                                else{
                                    $response = array(
                                        "status" => "notInUse",
                                        "message" => "Esta estacion no esta en uso.",
                                        "userName" => $userName,
                                        "userCode" => $code
                                    );
                                }
                            }
                            else {
                                $response = array(
                                    "status" => "non-existent",
                                    "message" => "Estacion inexistente.",
                                    "userName" => $userName,
                                    "userCode" => $code
                                );
                            }
            }
            else{
                $response = array(
                    "status" => "already-accompanying",
                    "message" => "Usted ya esta acompañando a alguien en otra estacion, no puede estar en mas de una estacion",
                    "userName" => $userName,
                    "userCode" => $code
                ); 
            }

         }
          else{
            $response = array(
                "status" => "already-using-a-station",
                "message" => "Usted ya tiene una estacion, no puede acompañar a este usuario a menos que registre su salida de la misma",
                "userName" => $userName,
                "userCode" => $code
            ); 
          }     

        } 
        else {
            $response = array(
                "status" => "userNotFound",
                "message" => "Usuario no registrado en la base de datos.",
                
            ); 
        }

    } catch (PDOException $e) {
        $response = array(
            "status" => "dbConnectionError",
            "message" => "Error de conexión a la base de datos."
        );
    }

} else {
    $response = array(
        "status" => "error",
        "message" => "Datos JSON no válidos."
    );
}






function findUser($conn, $serialNumber){
    $stmt = $conn->prepare("SELECT Nombre, Codigo FROM usuarios WHERE serialNumber = :serialNumber");
    $stmt->bindParam(':serialNumber', $serialNumber);
    $stmt->execute();
    return $stmt;
}

function getUserData($result) {
    global $userName, $code;
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $userName = $row["Nombre"];
    $code = $row["Codigo"];
}

function findStation($conn, $station) {
    $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = :station";
    $stmt = $conn->prepare($STAQuery);
    $stmt->bindParam(':station', $station);
    $stmt->execute();
    return $stmt;
}

function checkAvailability($conn, $station) {
    $estado = 'ocupado';
    $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = :station AND Estado = :estado";
    $stmt = $conn->prepare($STAQuery);
    $stmt->bindParam(':station', $station);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();
    return $stmt;
}

function updateEntryStation($conn, $lastRecordID, $userName){
    $updateQuery = "UPDATE registro_uso_estaciones SET Acomp = :userName WHERE ID = :lastRecordID";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':userName', $userName);
    $stmt->bindParam(':lastRecordID', $lastRecordID);

    return $stmt->execute();
}


function isUserHavingStation($conn, $code){
    $query = "SELECT Tipo FROM registro_uso_estaciones WHERE Codigo = :codigo ORDER BY ID DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':codigo', $code);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordType = $row['Tipo'];

        return $recordType == "Entrada";
    }

    return false;
}

function isUserAccompanying($conn, $user){
    $query = "SELECT Tipo FROM registro_uso_estaciones WHERE Acomp = :user ORDER BY ID DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user', $user);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordType = $row['Tipo'];

        return $recordType == "Entrada";
    }

    return false;
}


function findWorkstation($conn, $station){
    $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = :station";
    $stmt = $conn->prepare($STAQuery);
    $stmt->bindParam(':station', $station);
    $stmt->execute();
    return $stmt;
}


function getStationAndRecordInfo($conn, $station){
    $query = "SELECT Acomp, ID FROM registro_uso_estaciones WHERE Estacion = :station ORDER BY ID DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':station', $station);
    $stmt->execute();

    return $stmt;
}


$jsonData = json_encode($response);
echo $jsonData;

?>
