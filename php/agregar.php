<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
</head>
<body>

<h1>Agregar Usuario</h1>

<form action="agregar.php" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" required><br><br>

    <label for="correo">Correo:</label>
    <input type="email" name="correo" required><br><br>

    <label for="contrasena">Contraseña:</label>
    <input type="password" name="contrasena" required><br><br>

    <label for="rol">Rol:</label>
    <select name="rol" required>
        <option value="1">Profesor</option>
        <option value="2">Estudiante</option>
        <option value="3">Administrador</option>
    </select><br><br>

    <input type="submit" value="Agregar Usuario">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'conexion_be.php';

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = hash('sha512', $_POST['contrasena']); // Hash de la contraseña
    $rol = $_POST['rol'];

    $query = "INSERT INTO login (nombre, correo, contrasena, rol) VALUES ('$nombre', '$correo', '$contrasena', '$rol')";
    if (mysqli_query($conexion, $query)) {
        echo "Usuario agregado exitosamente.";
        header("Location: Administrador.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>
<a href="index.php">Volver a la lista de usuarios</a>

</body>
</html>
