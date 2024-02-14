<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Uso de Estaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input[type="text"],
        .search-form select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        .search-form input[type="submit"] {
            padding: 8px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historial de Uso de Estaciones</h1>

        <form class="search-form" method="GET" action="">
            <label for="codigo">Buscar por Código:</label>
            <input type="text" id="codigo" name="codigo" placeholder="Ingrese el código">
            <br>
            <label for="estatus">Seleccionar Estatus:</label>
            <select id="estatus" name="estatus">
                <option value="">Todos</option>
                <option value="ocupado">Ocupado</option>
                <option value="desocupado">Desocupado</option>
            </select>
            <br>
            <input type="submit" value="Buscar">
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Código</th>
                <th>Fecha y Hora de Entrada</th>
                <th>Fecha y Hora de Salida</th>
                <th>Estación</th>
                <th>Acompañante</th>
                <th>Estatus</th>
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

            // Filtrar por código si se ha enviado un código en el formulario
            if(isset($_GET['codigo']) && $_GET['codigo'] != '') {
                $codigo = $_GET['codigo'];
                $sql .= " WHERE Codigo = '$codigo'";
            }

            // Filtrar por estatus "ocupado" o "desocupado"
            $estatus = $_GET['estatus'] ?? ''; // Obtener el valor del parámetro estatus o establecer una cadena vacía si no se proporciona

            if($estatus === 'ocupado' || $estatus === 'desocupado') {
                if(strpos($sql, 'WHERE') === false) {
                    $sql .= " WHERE Estatus = '$estatus'";
                } else {
                    $sql .= " AND Estatus = '$estatus'";
                }
            }

            // Si ya hay una cláusula ORDER BY en la consulta, no la agregamos nuevamente
            if(strpos($sql, 'ORDER BY') === false) {
                $sql .= " ORDER BY ID DESC";
            }

            $result = $conn->query($sql);

            // Mostrar los registros en la tabla
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>".$row["ID"]."</td>
                            <td>".$row["Nombre"]."</td>
                            <td>".$row["Codigo"]."</td>
                            <td>".date("d-m-Y H:i:s", $row["Fecha_Y_Hora_E"] -  (6 * 3600))."</td>
                            <td>".($row["Fecha_Y_Hora_S"] != 0 ? date("d-m-Y H:i:s", $row["Fecha_Y_Hora_S"] -  (6 * 3600)) : "------")."</td>
                            <td>".$row["Estacion"]."</td>
                            <td>".$row["Acomp"]."</td>
                            <td>".$row["Estatus"]."</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay registros</td></tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>
</body>
</html>
