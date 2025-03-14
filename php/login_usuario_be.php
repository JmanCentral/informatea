<?php
session_start(); // Iniciar la sesión
include 'conexion_be.php'; // Conexión a la base de datos

// Obtener los datos del formulario
$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// Encriptar la contraseña con hash sha512
$contrasena = hash('sha512', $contrasena);

// Verificar si el usuario existe en la base de datos
$validar_login = mysqli_query($conexion, "SELECT * FROM login WHERE correo='$correo' AND contrasena='$contrasena'");

if (mysqli_num_rows($validar_login) > 0) {
    // Obtener los datos del usuario
    $row = mysqli_fetch_assoc($validar_login);
    $rol = $row['rol'];

    // Guardar el correo del usuario en la sesión
    $_SESSION['correo'] = $correo;

    // Devolver una respuesta JSON
    echo json_encode([
        'success' => true,
        'redirect' => $rol == 2 ? 'CursosEstudiantes.php' : ($rol == 1 ? 'Profesor.php' : 'Administrador.php')
    ]);
    exit();
} else {
    // Devolver una respuesta JSON con el mensaje de error
    echo json_encode([
        'success' => false,
        'message' => 'Usuario o contraseña incorrectos. Por favor, verifique los datos ingresados.'
    ]);
    exit();
}
?>