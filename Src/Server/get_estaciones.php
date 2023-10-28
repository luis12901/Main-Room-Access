<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laboratorio";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener el estado de las estaciones
$sql = "SELECT ST_ID, Estado FROM estaciones";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $estacionID = $row["ST_ID"];
        $estado = $row["Estado"];

        // Define la clase CSS según el estado
        $class = ($estado == 'Disponible') ? 'disponible' : 'ocupado';

        // Genera el HTML para la estación
        echo '<div class="estacion ' . $class . '">Estación ' . $estacionID . '</div>';
    }
} else {
    echo "No se encontraron estaciones en la base de datos.";
}

$conn->close();
?>
