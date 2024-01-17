<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true);

date_default_timezone_set('America/Mexico_City');

$fecha = new DateTime();
$timestamp = $fecha->getTimestamp();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laboratorio";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$serialNumber = $data['serialNumber'];
$numeroEstaciones = $data['numeroEstaciones'];

// Función para verificar si un usuario es un profesor
function esProfesor($conn, $serialNumber) {
    $query = "SELECT * FROM usuarios WHERE serialNumber = '$serialNumber' AND Tipo_Usuario = 'Maestro'";
    $result = $conn->query($query);
    return ($result->num_rows > 0);
}

// Función para verificar el estado del registro_uso_estaciones
function estadoRegistroEstaciones($conn, $profesorId) {
    $query = "SELECT Tipo FROM registro_uso_estaciones WHERE usuario_id = '$profesorId' ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $ultimoRegistro = $result->fetch_assoc();
        return $ultimoRegistro['Tipo'];
    }

    return null; // No hay registros previos
}

// Función para asignar estaciones y actualizar el registro_uso_estaciones
function asignarEstaciones($conn, $profesorId, $numeroEstaciones) {
    // Verificar si el número de estaciones solicitadas excede la disponibilidad
    $queryDisponibilidad = "SELECT COUNT(*) AS numDisponibles FROM estaciones WHERE Estado = 'disponible'";
    $resultDisponibilidad = $conn->query($queryDisponibilidad);
    $rowDisponibilidad = $resultDisponibilidad->fetch_assoc();
    $numDisponibles = $rowDisponibilidad['numDisponibles'];

    if ($numeroEstaciones > $numDisponibles) {
        echo json_encode(array('mensaje' => 'El número de estaciones solicitadas excede la disponibilidad del laboratorio.'));
        return;
    }

    // Obtener las estaciones disponibles para asignar
    $queryEstacionesDisponibles = "SELECT id FROM estaciones WHERE Estado = 'disponible' LIMIT $numeroEstaciones";
    $resultEstacionesDisponibles = $conn->query($queryEstacionesDisponibles);

    $estacionesAsignadas = array();
    while ($rowEstacion = $resultEstacionesDisponibles->fetch_assoc()) {
        $estacionesAsignadas[] = $rowEstacion['id'];
    }

    // Iniciar la transacción
    $conn->begin_transaction();

    // Insertar el registro en registro_uso_estaciones
    $horaEpoch = time();
    $queryRegistroEstaciones = "INSERT INTO registro_uso_estaciones (usuario_id, nombre, codigo, estaciones_apartadas, hora, Tipo)
              VALUES ('$profesorId', 'Profesor Ejemplo', 'CodigoEjemplo', '$numeroEstaciones', '$horaEpoch', 'Entrada')";
    $conn->query($queryRegistroEstaciones);

    // Actualizar el estado de las estaciones a 'ocupado'
    foreach ($estacionesAsignadas as $estacionId) {
        $queryActualizarEstacion = "UPDATE estaciones SET Estado = 'ocupado' WHERE id = '$estacionId'";
        $conn->query($queryActualizarEstacion);
    }

    // Confirmar la transacción
    $conn->commit();

    echo json_encode(array('mensaje' => 'Estaciones asignadas con éxito.'));
}

// Verificar si el usuario es un profesor
if (esProfesor($conn, $serialNumber)) {
    $query = "SELECT id FROM usuarios WHERE serialNumber = '$serialNumber'";
    $result = $conn->query($query);
    $profesor = $result->fetch_assoc();
    $profesorId = $profesor['id'];

    // Verificar el estado del registro_uso_estaciones
    $estadoRegistro = estadoRegistroEstaciones($conn, $profesorId);

    if ($estadoRegistro == 'Entrada') {
        // El profesor ya tiene estaciones apartadas
        echo json_encode(array('mensaje' => 'Ya tiene una o más estaciones apartadas. Desocupe estas estaciones y vuelva a intentarlo por favor.'));
    } else {
        // El profesor puede usar la opción, asignar estaciones
        asignarEstaciones($conn, $profesorId, $numeroEstaciones);
    }
} else {
    // El usuario no es un profesor
    echo json_encode(array('mensaje' => 'No puede usar esta opción, ya que no es un profesor.'));
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
