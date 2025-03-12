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

    // Redirigir según el rol del usuario
    if ($rol == 2) {
        header("Location: CursosEstudiantes.php");
    } elseif ($rol == 1) {
        header("Location: Profesor.php");
    } elseif ($rol == 3) {
        header("Location: Administrador.php");
    }
    exit();
} else {
    // Si los datos no son válidos, mostrar un mensaje de error y redirigir al login
    echo '
    <script>
        alert("Usuario no existe, por favor verifique los datos ingresados");
        window.location="login.php";
    </script>
    ';
    exit();
}
?>
