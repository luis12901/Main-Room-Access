<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true);

date_default_timezone_set('America/Mexico_City');

$fecha = new Datetime();
$timestamp = $fecha->getTimestamp();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laboratorio";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$serialNumber = $data['serialNumber'];


$userResult = findUser($conn, $serialNumber);

if ($userResult->num_rows > 0) {

    getUserData($userResult);

    $recordResult = recordType($conn, $code);
    $STA = $recordResult["STA"];
    $type = $recordResult["Type"];

            if($type){

                    $eventType =  "Entrada";

                    $stationResult = findAvailableStation($conn);

                    if ($stationResult->num_rows > 0) {

                            getStationData($stationResult);
                            
                            if (updateStationStatus($conn, $stationID)) {
                                        
                                insertEntryUsageRecord($conn, $userName, $code, $timestamp, $eventType, $stationID);

                            } 
                            else {echo "Error al actualizar el estado en la tabla registropersonaludg: " . $conn->error;}

                        
                    }
                    else{
                        echo "No hay estaciones disponibles, vuelva mas tarde.";
                    }

            } 
            else{
                $eventType =  "Salida";
                

                $entryStationData = getEntryStation($conn, $code);
                $STA = $entryStationData['STA'];
                $partnerID = $entryStationData['partner'];


                if(insertExitUsageRecord($conn, $userName, $code, $timestamp, $eventType, $STA, $partnerID)){
                
                        if( setAvailableStation($conn, $STA)){
                            echo ' Registro completo ';
                        }
                        else{echo ' Hubo un problema con el registro, porfavor llame a soporte tecnico. (Error 002) ';}
                
                }
                else{echo 'Hubo un error, porfavor llame a soporte tecnico. (Error 001)';}
            }
}
else{  
    echo "No esta registrado en nuestra base de datos";
}



function findUser($conn, $serialNumber) {
    $userQuery = "SELECT * FROM usuarios WHERE serialNumber = '$serialNumber'";
    $userResult = $conn->query($userQuery);
    return $userResult;
}
function findAvailableStation($conn) {
    $stationQuery = "SELECT * FROM estaciones WHERE Estado = 'disponible' ORDER BY ID DESC LIMIT 1";
    $stationResult = $conn->query($stationQuery);
    return $stationResult;
}
function updateStationStatus($conn, $stationID) {
    $updateQuery = "UPDATE estaciones SET Estado = 'ocupado' WHERE ST_ID = '$stationID'";
    $conn->query($updateQuery);
    return $conn->query($updateQuery) === TRUE; 
}
function insertEntryUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationID) {
    $partner = 'NA';

    $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acompañante) 
                    VALUES ('$userName', '$code' ,'$timestamp', '$recType', '$stationID', 'NA')";
    $conn->query($insertQuery);
}

function insertExitUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationUsed, $partnerID) {
    $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acompañante) 
                    VALUES ('$userName', '$code' ,'$timestamp', '$recType', '$stationUsed', '$partnerID')";
    
    if ($conn->query($insertQuery) === TRUE) {
        return true;
    } else {
        return false;
    }
}



function getUserData($result) {
        global $userName, $code;
    
        $row = $result->fetch_assoc();
        $userName = $row["Nombre"];
        $code = $row["Codigo"];
   
}
function getStationData($staResult){

        global $stationID;
        $stationRow = $staResult->fetch_assoc();
        $stationID = $stationRow["ST_ID"];

}
function recordType($conn, $user_code) {
    
    $query = "SELECT Tipo FROM registro_uso_estaciones WHERE Codigo = '$user_code' ORDER BY ID DESC LIMIT 1";
    $result = $conn->query($query);

    $STA = null;

    if ($result->num_rows > 0) {


        $row = $result->fetch_assoc();
        $recordType = $row['Tipo'];
        $STA = $row['Estacion'];  

        if ($recordType == "Salida") {
             return array("STA" => $STA, "Type" => true);
        } else {
            return array("STA" => $STA, "Type" => false);
        }
    }

    return array("STA" => $STA, "Type" => true);
}

function getEntryStation($conn, $user_code){
    $query = "SELECT Estacion, Acompañante FROM registro_uso_estaciones WHERE Codigo = '$user_code' ORDER BY ID DESC LIMIT 1";
    $result = $conn->query($query);

    $data = array(
        'STA' => null,
        'partner' => null
    );

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data['STA'] = $row['Estacion'];
        $data['partner'] = $row['Acompañante'];
    }

    return $data;
}


function setAvailableStation($conn, $entryStation) {
    $updateQuery = "UPDATE estaciones SET Estado = 'disponible' WHERE ST_ID = '$entryStation'";
    
    if ($conn->query($updateQuery) === TRUE) {
        return true;
    } else {
        return false;
    }
}


$conn->close();
?>