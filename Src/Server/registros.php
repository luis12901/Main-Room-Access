<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Uso de Estaciones</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Registros de Uso de Estaciones</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Código</th>
            <th>Fecha y Hora de Entrada</th>
            <th>Fecha y Hora de Salida</th>
            <th>Estación</th>
            <th>Acompañante</th>
        </tr>
        <?php
        // Conexión a la base de datos
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "laboratorio";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar la conexión
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        // Consulta SQL para obtener los registros de la tabla
        $sql = "SELECT * FROM registro_uso_estaciones";
        $result = $conn->query($sql);

        // Mostrar los registros en la tabla
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".$row["ID"]."</td>
                        <td>".$row["Nombre"]."</td>
                        <td>".$row["Codigo"]."</td>
                        <td>".date("Y-m-d H:i:s", $row["Fecha_Y_Hora_Entrada"])."</td>
                        <td>".date("Y-m-d H:i:s", $row["Fecha_Y_Hora_Salida"])."</td>
                        <td>".$row["Estacion"]."</td>
                        <td>".$row["Acomp"]."</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay registros</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
