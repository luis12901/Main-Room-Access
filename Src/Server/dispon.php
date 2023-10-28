<!DOCTYPE html>
<html>
<head>
    <title>Estado de las Estaciones de Trabajo</title>
    <style>
        body {
            text-align: center;
        }

        .titulo {
            font-size: 32px;
            margin: 20px 0;
            background-color: green;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
        }

        .mesa {
    width: 100px;
    height: 80px;
    margin: 10px;
    display: inline-block;
    text-align: center;
    font-size: 16px;
    border: 2px solid #000;
    border-radius: 10px;
    padding: 10px;
    vertical-align: top; /* Ajustar la alineación vertical */
}


        .disponible {
            background-color: green;
            color: white;
        }

        .ocupado {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <h1 class="titulo">Estado de las Estaciones de Trabajo</h1>
    <div class="estaciones-container">
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
                $nombre = ""; // Inicializar la variable nombre

                if ($estado == 'ocupado') {
                    // Realizar una consulta adicional a la tabla "registro_uso_estaciones"
                    $registroQuery = "SELECT Nombre FROM registro_uso_estaciones WHERE Estacion = '$estacionID' ORDER BY ID DESC LIMIT 1";

                    $registroResult = $conn->query($registroQuery);

                    if ($registroResult->num_rows > 0) {
                        $registroRow = $registroResult->fetch_assoc();
                        $nombre = $registroRow["Nombre"];
                    }
                }

                $class = ($estado == 'disponible') ? 'disponible' : 'ocupado';

                echo '<div class="mesa ' . $class . '"> ' . $estacionID;
                if ($nombre != "") {
                    echo '<br>Usuario: ' . $nombre;
                }
                echo '</div>';
            }
        } else {
            echo "No se encontraron estaciones en la base de datos.";
        }

        $conn->close();
        ?>
    </div>
    
    <script>
        // Actualizar la página cada 3 segundos
        setTimeout(function() {
            location.reload();
        }, 3000);
    </script>
</body>
</html>
