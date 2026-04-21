<?php
$host = "127.0.0.1";
$port = 3307; // El puerto debe ser un número o string aquí
$user = "root";
$pass = "root123"; 
$db   = "bd_tienda";

// El orden correcto es: host, user, pass, db, port
$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    // Si falla, intentamos sin puerto por si acaso, pero con la pass
    $conexion = mysqli_connect($host, $user, $pass, $db);
}

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>
