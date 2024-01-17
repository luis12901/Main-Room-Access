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




        $stmt = findUser($conn, $serialNumber);

        if ($stmt->rowCount() > 0) {

            getUserData($stmt);
            
            
            $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = :station";
            $stmt = $conn->prepare($STAQuery);
            $stmt->bindParam(':station', $station);
            $stmt->execute();
           

            if ($stmt->rowCount() > 0) {

                $stmt = checkAvailability($conn, $station);         // We use checkAvailability() to verify 
                if($stmt->rowCount() > 0){                                 //if there's only one persone using this station
                    
                    $query = "SELECT Acomp, ID FROM registro_uso_estaciones WHERE Estacion = :station ORDER BY ID DESC LIMIT 1";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':station', $station);
                    $stmt->execute();
                
                    if ($stmt->rowCount() > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $partner = $row['Acomp'];
                        $lastRecordID = $row['ID'];
                        
                        

                        if ($partner == 'NA') {
                                   
                            if(updateEntryStation($conn, $lastRecordID, $userName)){

                                $response = array(
                                    "status" => "success",
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


$jsonData = json_encode($response);
echo $jsonData;

?>
