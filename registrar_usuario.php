<?php
include("conexion.php");

if (isset($_POST['registrar'])) {
    // Usamos los nombres de la base de dayos
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $pass   = mysqli_real_escape_string($conexion, $_POST['password']);
    $rol    = "Cliente"; 

    $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES ('$nombre', '$correo', '$pass', '$rol')";
    
    try {
        if (mysqli_query($conexion, $sql)) {
            // aqui si ya esta bien te regresa al index con mensaje de éxito
            header("Location: index.php?registro=exitoso");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            // mensaje en cazo de que ya exista ese correo 
            echo "<script>alert('Error: El correo ya existe.'); window.location='index.php';</script>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
