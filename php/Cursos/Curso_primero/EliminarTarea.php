<?php
include __DIR__ . '/../../conexion_be.php'; // Conexión a la base de datos

$tarea_id = $_GET['id'] ?? '';
$archivo = $_GET['archivo'] ?? '';

// Validar que el ID de la tarea exista
if (empty($tarea_id)) {
    die('<p class="alert alert-danger">ID de tarea no proporcionado.</p>');
}

// Eliminar el archivo físico si existe
if (file_exists($archivo)) {
    unlink($archivo); // Eliminar archivo
}

// Eliminar el registro de la base de datos
$query = "DELETE FROM tareas WHERE id = $tarea_id";
if ($conexion->query($query) === TRUE) {
    echo '<p class="alert alert-success">Tarea eliminada correctamente.</p>';
} else {
    echo '<p class="alert alert-danger">Error al eliminar la tarea.</p>';
}

// Redirigir de vuelta a la página anterior
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
