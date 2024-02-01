<?php
header('Content-Type: application/json');

// Recibe los datos JSON del cuerpo de la solicitud POST
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);

$fecha = new Datetime();
$timestamp = $fecha->getTimestamp();
if ($data && isset($data->serialNumber)) {
    $serialNumber = $data->serialNumber;

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

        function recordType($conn, $user_code){
            $query = "SELECT Tipo, Estacion FROM registro_uso_estaciones WHERE Codigo = :user_code ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_code', $user_code);
            $stmt->execute();

            $STA = null;

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $recordType = $row['Tipo'];
                $STA = isset($row['Estacion']) ? $row['Estacion'] : null;

                if ($recordType == "Salida") {
                    return array("STA" => $STA, "Type" => true);
                } else {
                    return array("STA" => $STA, "Type" => false);
                }
            }

            return array("STA" => $STA, "Type" => true);
        }

        function findAvailableStation($conn){
            $stationQuery = "SELECT * FROM estaciones WHERE Estado = 'disponible' ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($stationQuery);
            $stmt->execute();
            
            return $stmt;
        }

        function getStationData($staResult){
            global $stationID;
            $stationRow = $staResult->fetch(PDO::FETCH_ASSOC); // Utilizar FETCH_ASSOC para obtener un array asociativo
            $stationID = $stationRow["ST_ID"];
        }

        function updateStationStatus($conn, $stationID){
            $updateQuery = "UPDATE estaciones SET Estado = 'ocupado' WHERE ST_ID = :stationID";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bindParam(':stationID', $stationID);
            $result = $stmt->execute();

            if ($result === true) {
                return $stationID; // Retornar $stationID si la actualización fue exitosa
            } else {
                return false;
            }
        }

        function insertEntryUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationID){
            $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acomp) 
                            VALUES (:userName, :code, :timestamp, :recType, :stationID, 'NA')";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':userName', $userName);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->bindParam(':recType', $recType);
            $stmt->bindParam(':stationID', $stationID);

            // Ejecutar la consulta y devolver true si fue exitosa, false en caso de error
            return $stmt->execute();
        }

        function getEntryStation($conn, $user_code){
            $query = "SELECT Estacion, Acomp FROM registro_uso_estaciones WHERE Codigo = :user_code ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_code', $user_code);
            $stmt->execute();

            $data = array(
                'STA' => null,
                'partner' => null
            );

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data['STA'] = $row['Estacion'];
                $data['partner'] = $row['Acomp'];
            }

            return $data;
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

        function setAvailableStation($conn, $entryStation){
            $lastLetter = substr($entryStation, -1);

            if (strpos($entryStation, ',') !== false) {
                if(updateStationAvail($conn, $entryStation)){
                    return true;
                }
                else{
                    return false;
                }
            }
            else if(strtoupper($lastLetter) === 'P'){
            
                $updateQuery = "UPDATE estaciones_particulares SET Estado = 'disponible' WHERE ST_ID = :entryStation";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindParam(':entryStation', $entryStation);

                if ($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            }
            else{
                $updateQuery = "UPDATE estaciones SET Estado = 'disponible' WHERE ST_ID = :entryStation";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindParam(':entryStation', $entryStation);

                if ($stmt->execute()) {
                    return true;
                } else {
                    return false;
                }
            }

        }

        function checkLastRecordType($conn, $user_code){
            $query = "SELECT Tipo FROM registro_uso_estaciones WHERE Codigo = ? ORDER BY ID DESC LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $user_code);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($recordType);
                $stmt->fetch();

                return $recordType == "Salida";
            }

            return false;
        }

        
        function isUserNotAccompanying($conn, $userName) {
                $query = "SELECT Tipo FROM registro_uso_estaciones WHERE Acomp = :userName ORDER BY ID DESC LIMIT 1";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':userName', $userName);
                $stmt->execute();
        
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $recordType = $row['Tipo'];
                    if($recordType == "Entrada"){
                        return false;
                    }
                    else{
                        return true;
                    }
                    
                } 
                else {
                    return true;
                }
            
        }
        
        



        $stmt = findUser($conn, $serialNumber);

        if ($stmt->rowCount() > 0) {

                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $userName = $result['Nombre'];
                $code = $result['Codigo'];

                if(isUserNotAccompanying($conn, $userName)){
                        
                    $recordResult = recordType($conn, $code);
                    $STA = $recordResult["STA"];
                    $type = $recordResult["Type"];

                    if ($type) {            
                        
                        $eventType =  "Entrada";
                        $stationResult = findAvailableStation($conn);

                        if ($stationResult && $stationResult->rowCount() > 0) {


                            getStationData($stationResult);
                            

                            if (updateStationStatus($conn, $stationID)) {
                                if (insertEntryUsageRecord($conn, $userName, $code, $timestamp, $eventType, $stationID)) {
                                    $response = array(
                                        "status" => "success (E)",
                                        "message" => "Registro de entrada completado exitosamente",
                                        "userName" => $userName,
                                        "userCode" => $code,
                                        "userStation" => $stationID
                                    );
                                } else {
                                    $response = array(
                                        "status" => "error",
                                        "message" => "Error al realizar el registro",
                                        "userName" => $userName,
                                        "userCode" => $code
                                    );
                                }
                            } else {
                                $response = array(
                                    "status" => "error",
                                    "message" => "Error al actualizar el estado en la tabla 'estaciones'",
                                    "userName" => $userName,
                                    "userCode" => $code
                                );
                            }
                            
                        }
                        else{
                            $response = array(
                                "status" => "noStations",
                                "message" => "Lo sentimos, no hay estaciones disponibles por el momento, vuelva mas tarde.",
                                "userName" => $userName,
                                "userCode" => $code
                            );
                        } 
                    }
                    else{
                        
                        $eventType =  "Salida";
                        $entryStationData = getEntryStation($conn, $code);
                        $STA = $entryStationData['STA'];
                        $partnerID = $entryStationData['partner'];
                        
                        if (insertExitUsageRecord($conn, $userName, $code, $timestamp, $eventType, $STA, $partnerID)) {
                            if (setAvailableStation($conn, $STA)) {
                                $response = array(
                                    "status" => "success (S)",
                                    "message" => "Registro de salida completado exitosamente",
                                    "userName" => $userName,
                                    "userCode" => $code
                                );
                            } else {
                                $response = array(
                                    "status" => "error",
                                    "message" => "Hubo un problema con el registro, por favor llame a soporte técnico. ( Hubo un problema con la actualizacion del estado de la estacion. )",
                                    "userName" => $userName,
                                    "userCode" => $code
                                );
                            }
                        } else {
                            $response = array(
                                "status" => "sqlInsertError",
                                "message" => "Hubo un error, por favor llame a soporte técnico. ( Error de insercion en registro de salida )",
                                "userName" => $userName,
                                "userCode" => $code
                            );
                        }



                    }
           
            }
            else{
                $response = array(
                    "status" => "already-using-a-station",
                    "message" => "Ya esta usando una estacion compartida.",
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
