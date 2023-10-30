<?php

$fecha = new Datetime();
$timestamp = $fecha->getTimestamp();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laboratorio";

// Obtener los datos enviados
$data = json_decode(file_get_contents('php://input'), true);

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$serialNumber = $data['serialNumber'];
$station = $data['station'];


$userResult = findUser($conn, $serialNumber);
        
if ($userResult->num_rows > 0) {

        getUserData($userResult);

        $selectedstation = findStation($conn, $station);
        if ($selectedstation->num_rows > 0) {

            $Availability = checkAvailability($conn, $station);
            if($Availability->num_rows > 0){
                
                updateEntryStation($conn, $station, $userName, $code, $timestamp, $station);
                
            }
            else{
                echo 'Esta estacion esta desocupada';
            }
        }
        else {
            echo 'No existe esta estacion';
        }
}



function updateEntryStation($conn, $station, $userName, $code, $time, $STA) {
   
    $query = "SELECT Acompañante FROM registro_uso_estaciones WHERE Estacion = '$station' ORDER BY ID DESC LIMIT 1";
    $result = $conn->query($query);


    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $partner = $row['Acompañante'];

        if($partner == 'NA'){

            $query = "SELECT ID FROM registro_uso_estaciones WHERE Estacion = '$station' ORDER BY ID DESC LIMIT 1";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $lastRecordID = $row['ID'];

                
                $updateQuery = "UPDATE registro_uso_estaciones SET Acompañante = 'SI' WHERE ID = $lastRecordID";
                $conn->query($updateQuery);

                
                $insertQuery = "INSERT INTO acompañantes_estaciones (ID, Nombre, Codigo, Fecha_Y_Hora, Estacion) 
                                VALUES ('$lastRecordID', '$userName', '$code' ,'$time', '$STA')";
                $conn->query($insertQuery);
                
                
            }
        }
        else{
            echo ' Nada';
        }

           
    }
    else{
        echo 'No se ha encontrado infomacion de esta estacion';
    }
}


function findUser($conn, $serialNumber) {
    $userQuery = "SELECT * FROM usuarios WHERE serialNumber = '$serialNumber'";
    $userResult = $conn->query($userQuery);
    return $userResult;
}

function findStation($conn, $station) {
    $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = '$station'";
    $STAResult = $conn->query($STAQuery);
    return $STAResult;
}

function checkAvailability($conn, $station){
    $STAQuery = "SELECT * FROM estaciones WHERE ST_ID = '$station' AND Estado = 'ocupado'";
    $STAResult = $conn->query($STAQuery);
    return $STAResult;
}

function getUserData($result) {
    global $userName, $code;

    $row = $result->fetch_assoc();
    $userName = $row["Nombre"];
    $code = $row["Codigo"];

}

$conn->close();
?>
