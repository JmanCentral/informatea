<?php
include_once 'conexion_be.php';

// Recuperar datos del formulario
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$foto = $_POST['foto'];
$rol = $_POST['rol'];
$contrasena = $_POST['contrasena'];
$contrasena = hash('sha512', $contrasena);

// Verificar que el correo no se repita en la base de datos
$verificar_correo = mysqli_query($conexion, "SELECT * FROM login WHERE correo='$correo'");
if (mysqli_num_rows($verificar_correo) > 0) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="3;url=login.php">
        <title>Error</title>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f0f0f0;
                font-family: Arial, sans-serif;
            }
            .message {
                background-color: #fff;
                padding: 20px 40px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
                border: 1px solid #ddd;
            }
            .message p {
                font-size: 18px;
                color: #333;
            }
            .message.error {
                border-left: 5px solid #ff4d4d;
            }
            .message.success {
                border-left: 5px solid #4CAF50;
            }
        </style>
    </head>
    <body>
        <div class="message error">
            <p>Este correo ya está registrado, intenta con otro diferente.</p>
            <p>Redirigiendo en 3 segundos...</p>
        </div>
    </body>
    </html>
    ';
    exit();
}

// Insertar el nuevo usuario en la base de datos
$query = "INSERT INTO login (rol, nombre, correo, foto, contrasena) 
          VALUES ('$rol', '$nombre', '$correo', '$foto', '$contrasena')";
$ejecutar = mysqli_query($conexion, $query);

if ($ejecutar) {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="3;url=login.php">
        <title>Registro exitoso</title>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f0f0f0;
                font-family: Arial, sans-serif;
            }
            .message {
                background-color: #fff;
                padding: 20px 40px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
                border: 1px solid #ddd;
            }
            .message p {
                font-size: 18px;
                color: #333;
            }
            .message.success {
                border-left: 5px solid #4CAF50;
            }
        </style>
    </head>
    <body>
        <div class="message success">
            <p>Registro exitoso.</p>
            <p>Redirigiendo en 3 segundos...</p>
        </div>
    </body>
    </html>
    ';
} else {
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="3;url=login.php">
        <title>Error</title>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f0f0f0;
                font-family: Arial, sans-serif;
            }
            .message {
                background-color: #fff;
                padding: 20px 40px;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                text-align: center;
                border: 1px solid #ddd;
            }
            .message p {
                font-size: 18px;
                color: #333;
            }
            .message.error {
                border-left: 5px solid #ff4d4d;
            }
        </style>
    </head>
    <body>
        <div class="message error">
            <p>Error al almacenar el usuario. Inténtelo nuevamente.</p>
            <p>Redirigiendo en 3 segundos...</p>
        </div>
    </body>
    </html>
    ';
}

// Cerrar la conexión a la base de datos
mysqli_close($conexion);
?>