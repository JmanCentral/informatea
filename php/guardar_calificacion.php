<?php
include 'conexion_be.php'; // Conexión a la base de datos

session_start(); // Iniciar sesión

// Verificar si el profesor ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    echo '<p class="alert alert-danger">Debes iniciar sesión para calificar.</p>';
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_calificacion'])) {
    $respuesta_id = $_POST['respuesta_id'];
    $calificacion = $_POST['calificacion'];
    $observaciones = $_POST['observaciones'];
    $fecha_calificacion = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    // Validar que la calificación esté en el rango correcto
    if ($calificacion >= 0 && $calificacion <= 10) {
        // Insertar la calificación en la base de datos
        $query = "INSERT INTO calificaciones (calificacion, observaciones, fecha_calificacion, respuesta_id) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("dssi", $calificacion, $observaciones, $fecha_calificacion, $respuesta_id);

        if ($stmt->execute()) {
            // Mostrar la interfaz bonita si la calificación se guardó correctamente
            echo '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Calificación Guardada</title>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: "Poppins", sans-serif;
                        background: linear-gradient(135deg, #6a11cb, #2575fc);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        color: #fff;
                    }
                    .success-container {
                        text-align: center;
                        background: rgba(255, 255, 255, 0.1);
                        padding: 40px;
                        border-radius: 15px;
                        backdrop-filter: blur(10px);
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                        max-width: 400px;
                        width: 100%;
                    }
                    .success-container h1 {
                        font-size: 2.5rem;
                        margin-bottom: 20px;
                        font-weight: 600;
                    }
                    .success-container p {
                        font-size: 1.2rem;
                        margin-bottom: 30px;
                    }
                    .success-container .btn {
                        background: #fff;
                        color: #2575fc;
                        padding: 12px 30px;
                        border: none;
                        border-radius: 25px;
                        font-size: 1rem;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        text-decoration: none;
                        display: inline-block;
                    }
                    .success-container .btn:hover {
                        background: #2575fc;
                        color: #fff;
                        transform: translateY(-3px);
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                    }
                    .success-container .btn:active {
                        transform: translateY(0);
                    }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <h1>¡Éxito!</h1>
                    <p>La calificación ha sido guardada correctamente.</p>
                    <a href="cursos.php" class="btn">Volver a los Cursos</a>
                </div>
            </body>
            </html>
            ';
        } else {
            echo '<p class="alert alert-danger">Error al guardar la calificación.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">La calificación debe estar entre 0 y 10.</p>';
    }
} else {
    echo '<p class="alert alert-danger">Acceso no autorizado.</p>';
}
?>