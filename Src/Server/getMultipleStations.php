<?php
header('Content-Type: application/json');

$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);

$fecha = new Datetime();
$timestamp = $fecha->getTimestamp();

if ($data && isset($data->serialNumber) && isset($data->stationsNumber)) {
    $serialNumber = $data->serialNumber;
    $stationsNumber = $data->stationsNumber;

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "laboratorio";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        function findUser($conn, $serialNumber){
            $stmt = $conn->prepare("SELECT Nombre, Codigo, Tipo_Usuario FROM usuarios WHERE serialNumber = :serialNumber");
            $stmt->bindParam(':serialNumber', $serialNumber);
            $stmt->execute();
            return $stmt;
        }

        function getLastStationRecordType($conn, $code) {
            $query = "SELECT Estatus FROM registro_uso_estaciones WHERE Codigo = :code ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['Estatus'];
            }
            return 'desocupado';
        }

        function getEntryStation($conn, $code) {
            $query = "SELECT Estacion FROM registro_uso_estaciones WHERE Codigo = :code ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['Estacion'];
            }
            return null;
        }
        function getPartner($conn, $code) {
            $query = "SELECT Acomp FROM registro_uso_estaciones WHERE Codigo = :code ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['Acomp'];
            }
            return null;
        }

        function checkAvailableStations($conn, $stationsNumber) {
            $query = "SELECT COUNT(*) AS total FROM estaciones WHERE Estado = 'disponible'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalStations = $row['total'];
            return $totalStations >= $stationsNumber;
        }
        function findAvailableStation($conn) {
            $query = "SELECT ST_ID FROM estaciones WHERE Estado = 'disponible' ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->execute();
        
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stationID = $result['ST_ID'];
                return $stationID;
            } else {
                return null; 
            }
        }
        
        function updateStatus($conn, $stationID) {
                $updateQuery = "UPDATE estaciones SET Estado = 'ocupado' WHERE ST_ID = :stationID LIMIT 1";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindParam(':stationID', $stationID, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    return true; 
                } else {
                    return false; 
                }
        }
        function updateStationStatus($conn, $stationsNumber){
            $updatedStationsIDs = "";
        
            for ($i = 0; $i < $stationsNumber; $i++) {
                $currentStation = findAvailableStation($conn);
        
                if(updateStatus($conn, $currentStation)){
                    // Agregar el ID de la estación al string con comas
                    $updatedStationsIDs .= ($i > 0 ? "," : "") . $currentStation;
                } else {
                    $response = array(
                        "status" => "Error",
                        "message" => "Could not upload all registers.",
                        "userName" => $updatedStationsIDs,
                        "userCode" => $updatedStationsIDs
                    );
                    return $response;
                }
            }
        
            return $updatedStationsIDs;
        }
        function insertEntryUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationID){
            $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora_E, Fecha_Y_Hora_E, Estatus, Estacion, Acomp) 
                            VALUES (:userName, :code, :timestamp, :recType, :stationID, 'NA')";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->bindParam(':recType', $recType);
            $stmt->bindParam(':stationID', $stationID);

            return $stmt->execute();
        }
        function updateStationAvail($conn, $entryStation) {
            // Divide la cadena de entrada en IDs de estaciones individuales
            $stationIDs = explode(',', $entryStation);
        
            // Prepara la consulta para actualizar el estado de la estación
            $updateQuery = "UPDATE estaciones SET Estado = 'disponible' WHERE ST_ID = :stationID";
        
            // Prepara la instrucción de consulta
            $stmt = $conn->prepare($updateQuery);
        
            // Itera sobre cada ID de estación y actualiza su estado
            foreach ($stationIDs as $stationID) {
                // Asigna el valor del ID de estación a la instrucción preparada
                $stmt->bindParam(':stationID', $stationID, PDO::PARAM_STR);
                
                
                if($stmt->execute()){

                }
                else{
                    return false;
                }
            }
            return true;
        }
        function insertExitUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationUsed, $partnerID){
            $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acomp) 
                            VALUES (:userName, :code, :timestamp, :recType, :stationUsed, :partnerID)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->bindParam(':recType', $recType);
            $stmt->bindParam(':stationUsed', $stationUsed);
            $stmt->bindParam(':partnerID', $partnerID);

            return $stmt->execute();
        }


        $stmt = findUser($conn, $serialNumber);

        if ($stmt->rowCount() > 0) {

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $result['Nombre'];
            $code = $result['Codigo'];
            $user_type = $result['Tipo_Usuario'];

            if($user_type == 'Maestro'){
                // Verificar el último registro de estación para el usuario
                $lastRecordType = getLastStationRecordType($conn, $code);
                if ($lastRecordType == 'ocupado') {
                    
                    $eventType = 'desocupado';
                    $entryStation = getEntryStation($conn, $code);
                    $partner = getPartner($conn, $code);

                    if(updateStationAvail($conn, $entryStation)){

                        if(insertExitUsageRecord($conn, $userName, $code, $timestamp, $eventType, $entryStation, $partner)){

                            $response = array(
                                "status" => "success (S)",
                                "message" => "Registro de salida completado exitosamente",
                                "userName" => $userName,
                                "userCode" => $code
                            );

                        }
                        else{
                            $response = array(
                                "status" => "error",
                                "message" => "Hubo un problema con el registro, por favor llame a soporte técnico. ( Hubo un problema con la actualizacion del estado de la estacion. )",
                                "userName" => $userName,
                                "userCode" => $code
                            );
                        }


                    }
                    else{
                        $response = array(
                            "status" => "error",
                            "message" => "Error al actualizar el estado en la tabla 'estaciones'",
                            "userName" => $userName,
                            "userCode" => $code
                        );
                    }                   

                } 
                else {

                    $eventType = 'ocupado';
                    
                    if (checkAvailableStations($conn, $stationsNumber)) {

                        $updatedStationsIDs = updateStationStatus($conn, $stationsNumber);
                        
                        if (insertEntryUsageRecord($conn, $userName, $code, $timestamp, $eventType, $updatedStationsIDs)) {
                            $response = array(
                                "status" => "success (M)",
                                "message" => "Registro de entrada completado exitosamente",
                                "userName" => $userName,
                                "userCode" => $code,
                                "userStation" => $updatedStationsIDs
                            );
                        } 
                        else {
                            $response = array(
                                "status" => "error",
                                "message" => "Error al realizar el registro",
                                "userName" => $userName,
                                "userCode" => $code
                            );
                        }
                    
                    } 
                    else {
                        $response = array(
                            "status" => "insufficient-stations",
                            "message" => "No hay suficientes estaciones disponibles.",
                            "userName" => $userName,
                            "userCode" => $code
                        );
                    }
                }
            }
            else{
                $response = array(
                    "status" => "User-not-permitted",
                    "message" => "No tiene acceso a esta opción. (SOLO MAESTROS)",
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




    }
    catch (PDOException $e) {
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
