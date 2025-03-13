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

    echo '<p class="alert alert-success">Respuestas guardadas correctamente.</p>';
} else {
    echo '<p class="alert alert-danger">Error al procesar las respuestas.</p>';
}
?>
