<?php

header('Content-Type: application/json; charset=UTF-8');


// Recibe los datos JSON del cuerpo de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);


if ($data && isset($data->serialNumber)) {
    $serialNumber = $data->serialNumber;

    // Conecta a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "laboratorio";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userResult = findUser($conn, $serialNumber);

        if ($userResult && $userResult->rowCount() > 0) {
            getUserData($userResult);

            $recordResult = recordType($conn, $code);
            $STA = $recordResult["STA"];
            $type = $recordResult["Type"];

            if ($type) {
                $eventType =  "Entrada";
                $stationResult = findAvailableStation($conn);

                if ($stationResult && $stationResult->rowCount() > 0) {
                    getStationData($stationResult);
                    if (updateStationStatus($conn, $stationID)) {
                        insertEntryUsageRecord($conn, $userName, $code, $timestamp, $eventType, $stationID);
                        $response = array(
                            "status" => "success(E)",
                            "message" => "Registro completo exitosamente",
                            "username" => $userName
                        );
                    } else {
                        $response = array(
                            "status" => "error",
                            "message" => "Error al actualizar el estado en la tabla estaciones",
                            "username" => $userName
                        );
                    }
                } else {
                    $response = array(
                        "status" => "noStationsAv",
                        "message" => "No hay estaciones disponibles, vuelva más tarde.",
                        "username" => $userName
                    );
                }
            } else {
                $eventType =  "Salida";
                $entryStationData = getEntryStation($conn, $code);
                $STA = $entryStationData['STA'];
                $partnerID = $entryStationData['partner'];

                if (insertExitUsageRecord($conn, $userName, $code, $timestamp, $eventType, $STA, $partnerID)) {
                    if (setAvailableStation($conn, $STA)) {
                        $response = array(
                            "status" => "success(S)",
                            "message" => "Registro completo exitosamente",
                            "username" => $userName
                        );
                    } else {
                        $response = array(
                            "status" => "error",
                            "message" => "Hubo un problema con el registro, por favor llame a soporte técnico. ( Problema con la actualizacion del estado de la estacion. )",
                            "username" => $userName
                        );
                    }
                } else {
                    $response = array(
                        "status" => "sqlInsertError",
                        "message" => "Hubo un error, por favor llame a soporte técnico. ( Error de insercion en registro de salida )",
                        "username" => $userName
                    );
                }
            }
        } else {
            $response = array(
                "status" => "NotFound",
                "message" => "No está registrado en nuestra base de datos, por favor solicite su registro en el modulo de prestadores (N2)",
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

$conn = null; 
/*
$jsonData = json_encode($response);
echo $jsonData;

$conn->close();
*/
function findUser($conn, $serialNumber)
{
    $userQuery = "SELECT * FROM usuarios WHERE serialNumber = '$serialNumber'";
    $userResult = $conn->query($userQuery);
    return $userResult;
}

function findAvailableStation($conn)
{
    $stationQuery = "SELECT * FROM estaciones WHERE Estado = 'disponible' ORDER BY ID DESC LIMIT 1";
    $stationResult = $conn->query($stationQuery);
    return $stationResult;
}

function updateStationStatus($conn, $stationID)
{
    $updateQuery = "UPDATE estaciones SET Estado = 'ocupado' WHERE ST_ID = '$stationID'";
    $result = $conn->query($updateQuery);

    if ($result === TRUE) {
        return $stationID; // Retornar $stationID si la actualización fue exitosa
    } else {
        return false;
    }
}

function insertEntryUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationID)
{
    $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acomp) 
                    VALUES ('$userName', '$code' ,'$timestamp', '$recType', '$stationID', 'NA')";
    $conn->query($insertQuery);
}

function insertExitUsageRecord($conn, $userName, $code, $timestamp, $recType, $stationUsed, $partnerID)
{
    $insertQuery = "INSERT INTO registro_uso_estaciones (Nombre, Codigo, Fecha_Y_Hora, Tipo, Estacion, Acomp) 
                    VALUES ('$userName', '$code' ,'$timestamp', '$recType', '$stationUsed', '$partnerID')";

    if ($conn->query($insertQuery) === TRUE) {
        return true;
    } else {
        return false;
    }
}

function getUserData($result)
{
    global $userName, $code;

    $row = $result->fetch_assoc();
    $userName = $row["Nombre"];
    $code = $row["Codigo"];
}

function getStationData($staResult)
{
    global $stationID;
    $stationRow = $staResult->fetch_assoc();
    $stationID = $stationRow["ST_ID"];
}

function recordType($conn, $user_code)
{
    $query = "SELECT Tipo, Estacion FROM registro_uso_estaciones WHERE Codigo = '$user_code' ORDER BY ID DESC LIMIT 1";
    $result = $conn->query($query);

    $STA = null;

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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
function getEntryStation($conn, $user_code)
{
    $query = "SELECT Estacion, Acomp FROM registro_uso_estaciones WHERE Codigo = '$user_code' ORDER BY ID DESC LIMIT 1";
    $result = $conn->query($query);

    $data = array(
        'STA' => null,
        'partner' => null
    );

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data['STA'] = $row['Estacion'];
        $data['partner'] = $row['Acomp'];
    }

    return $data;
}

function setAvailableStation($conn, $entryStation)
{
    $updateQuery = "UPDATE estaciones SET Estado = 'disponible' WHERE ST_ID = '$entryStation'";

    if ($conn->query($updateQuery) === TRUE) {
        return true;
    } else {
        return false;
    }
}


?>
