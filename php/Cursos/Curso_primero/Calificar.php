<?php
include __DIR__ . '/../../conexion_be.php'; // Conexión a la base de datos

$tarea_id = $_GET['id'] ?? '';
$estudiante = $_GET['estudiante'] ?? '';

// Validar que el ID de la tarea exista
if (empty($tarea_id)) {
    die('<p class="alert alert-danger">No se ha proporcionado un ID de tarea válido.</p>');
}

// Procesar el formulario de calificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificacion = $_POST['calificacion'];

    $query = "UPDATE tareas SET calificacion = '$calificacion' WHERE id = $tarea_id";
    if ($conexion->query($query) === TRUE) {
        echo '<p class="alert alert-success">Calificación guardada correctamente.</p>';
    } else {
        echo '<p class="alert alert-danger">Error al guardar la calificación.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificar Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Calificar Tarea de: <?php echo htmlspecialchars($estudiante); ?></h1>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label for="calificacion" class="form-label">Calificación (0-100)</label>
            <input type="number" name="calificacion" id="calificacion" min="0" max="100" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar Calificación</button>
    </form>
</div>
</body>
</html>
