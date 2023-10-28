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


        // Primero verificamos que existe el ususario

        // Luego verificamos si hay estaciones de trabajo sin equipo disponibles

        // Procedemos a si hay estaciones disponibles, pasamos actualizar el estado de la estacion y darle acceso al estudainte o maestro




        $conn->close();
        ?>