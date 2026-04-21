<?php
include("conexion.php");

if (isset($_POST['btn_crear'])) {
    //  datos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $passwordPlano = $_POST['password'];
    
    //  contraseña
    $passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

    // Si el correo termina exactamente en @visored.com, es Admin.
    if (substr($correo, -12) === '@visored.com') {
        $rol = "Administrador";
    } else {
        $rol = "Cliente";
    }
    // consulta SQL 
    $sql = "INSERT INTO usuarios (nombre, apellidos, telefono, correo, password, rol) 
            VALUES ('$nombre', '$apellidos', '$telefono', '$correo', '$passwordHash', '$rol')";

    try {
        if (mysqli_query($conexion, $sql)) {
            header("Location: index.php?registro=exitoso");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "<script>alert('El correo ya está registrado'); window.history.back();</script>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta Nueva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #4682B4; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card-registro { background: white; padding: 2rem; border-radius: 15px; width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="card-registro">
        <h3 class="text-center fw-bold mb-4">Registro de Usuario</h3>
        <form method="POST">
            <input type="text" name="nombre" class="form-control mb-2" placeholder="Nombre(s)" required>
            <input type="text" name="apellidos" class="form-control mb-2" placeholder="Apellidos" required>
            <input type="text" name="telefono" class="form-control mb-2" placeholder="Número de teléfono" required>
            <input type="email" name="correo" class="form-control mb-2" placeholder="Correo electrónico " required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Crea una contraseña" required>
            
            <button type="submit" name="btn_crear" class="btn btn-primary w-100 fw-bold">CREAR CUENTA</button>
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none small">Ya tengo cuenta, volver al inicio</a>
            </div>
        </form>
    </div>
</body>
</html>