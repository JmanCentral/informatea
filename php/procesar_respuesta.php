<?php
include 'conexion_be.php';
session_start();

// Verificar que el estudiante haya iniciado sesión
if (!isset($_SESSION['correo'])) {
    die("Error: No has iniciado sesión.");
}

$estudiante_correo = $_SESSION['correo']; // Correo del estudiante

// Verificar que los datos fueron enviados correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evaluacion_id'], $_POST['respuestas'])) {
    $evaluacion_id = $_POST['evaluacion_id'];
    $curso_id = $_POST['curso_id'];

    foreach ($_POST['respuestas'] as $pregunta_id => $respuesta) {
        // Prevenir inyección SQL escapando los valores
        $pregunta_id = (int) $pregunta_id;
        $respuesta = $conexion->real_escape_string($respuesta);

        // Insertar la respuesta del estudiante en la base de datos
        $query = "INSERT INTO respuestas_estudiantes (evaluacion_id, estudiante_correo, pregunta_id, respuesta) 
                  VALUES ($evaluacion_id, '$estudiante_correo', $pregunta_id, '$respuesta')";

        if (!$conexion->query($query)) {
            echo "Error al guardar la respuesta: " . $conexion->error;
        }
    }

    // Mostrar mensaje de éxito con diseño avanzado
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Respuesta Enviada</title>
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
            <p>Tu respuesta ha sido enviada correctamente.</p>
            <a href="ver_curso.php?id=' . $curso_id . '" class="btn">Volver al Curso</a>
        </div>
    </body>
    </html>
    ';
} else {
    echo '<p class="alert alert-danger">Error al procesar las respuestas.</p>';
}
?>