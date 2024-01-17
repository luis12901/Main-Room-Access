<?php
header('Content-Type: application/json');

// Recibe los datos JSON del cuerpo de la solicitud POST
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData);

if ($data && isset($data->serialNumber)) {
    $serialNumber = $data->serialNumber;

    // Conecta a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "rfid";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Realiza la consulta en la base de datos
    $sql = "SELECT * FROM alta WHERE serialNumber = '$serialNumber'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // El usuario está registrado
        $response = array("status" => "success", "message" => "Usuario registrado en la base de datos.");
    } else {
        // El usuario no está registrado
        $response = array("status" => "error", "message" => "Usuario no registrado en la base de datos.");
    }

    // Cierra la conexión a la base de datos
    $conn->close();
} else {
    // Datos JSON no válidos
    $response = array("status" => "error", "message" => "Datos JSON no válidos.");
}

// Devuelve la respuesta en formato JSON
echo json_encode($response);
?>
