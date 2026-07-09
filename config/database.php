<?php
    $host     = "127.0.0.1";
    $usuario  = "root";
    $password = "";
    $database = "homedial";

    mysqli_report(MYSQLI_REPORT_OFF);
    $conectar = @mysqli_connect($host, $usuario, $password, $database);

    if (!$conectar) {
        die("Sin conexion activa a la base de datos. Revise que Laragon este corriendo, o el host/puerto en config/database.php.");
    }

    mysqli_set_charset($conectar, "utf8mb4");
?>
