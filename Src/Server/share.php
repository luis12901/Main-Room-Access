<?php
header('Content-Type: application/json; charset=UTF-8');

// Recibe los datos JSON del cuerpo de la solicitud POST
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


        function findUser($conn, $serialNumber) {
            $userQuery = "SELECT * FROM usuarios WHERE serialNumber = :serialNumber";
            $stmt = $conn->prepare($userQuery);
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
            $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = :station AND Estado = 'ocupado'";
            $stmt = $conn->prepare($STAQuery);
            $stmt->bindParam(':station', $station);
            $stmt->execute();
            return $stmt;
        }
        function updateEntryStation($conn, $station, $userName, $code, $time, $STA) {
            $query = "SELECT Acomp, ID FROM registro_uso_estaciones WHERE Estacion = :station ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':station', $station);
            $stmt->execute();
        
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $partner = $row['Acomp'];
                $lastRecordID = $row['ID'];
        
                if ($partner == 'NA') {
                    $updateQuery = "UPDATE registro_uso_estaciones SET Acomp = :userName WHERE ID = :lastRecordID";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bindParam(':userName', $userName);
                    $stmt->bindParam(':lastRecordID', $lastRecordID);
                    $stmt->execute();
                    // Agrega la actualización del código del estudiante y la fecha aquí, después de agregar las columnas correspondientes en la tabla MySQL
                } else {
                    echo 'Ya hay dos personas usando esta estacion';
                }
            } else {
                echo 'No se ha encontrado información de esta estacion';
            }
        }
        
        

        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = findUser($conn, $serialNumber);
                
        if ($stmt->num_rows > 0) {

                getUserData($stmt);

                $selectedstation = findStation($conn, $station);
                if ($selectedstation->num_rows > 0) {

                    $Availability = checkAvailability($conn, $station);         // We use checkAvailability() to verify 
                    if($Availability->num_rows > 0){                        //if there's truly someone using this station
                        
                        updateEntryStation($conn, $station, $userName, $code, $timestamp, $station);
                        $response = array(
                            "status" => "success(E)",
                            "message" => "Registro de entrada completado exitosamente",
                            "userName" => $userName,
                            "userCode" => $code
                        );
                        
                    }
                    else{
                        echo 'Esta estacion esta desocupada';
                    }
                }
                else {
                    echo 'Estacion inexistente.';
                }
        }
        else {
            $response = array(
                "status" => "userNotFound",
                "message" => "Usuario no registrado en la base de datos.",
                
            ); 
        }

    } 
    catch (PDOException $e) {
    $response = array(
        "status" => "dbConnectionError",
        "message" => "Error de conexión a la base de datos."
    );
    }

}
 else {
$response = array(
"status" => "error",
"message" => "Datos JSON no válidos."
);
}


$jsonData = json_encode($response);
echo $jsonData;

?>
?>
