<?php
include __DIR__ . '/../../conexion_be.php'; // Conexión a la base de datos

// Obtener los parámetros de la URL
$tarea_padre_id = $_GET['tarea_id'] ?? '';

// Validar que el parámetro no esté vacío
if (empty($tarea_padre_id)) {
    echo '<p class="alert alert-danger">No se ha especificado la tarea padre correctamente.</p>';
    exit;
}

// Consulta SQL para obtener las tareas subidas relacionadas
$query = "SELECT * FROM tareas WHERE tarea_padre_id = $tarea_padre_id";
$result = $conexion->query($query);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tareas Subidas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Tareas Subidas para la Tarea Padre ID: <?php echo $tarea_padre_id; ?></h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tarea ID</th>
                <th>Estudiante</th>
                <th>Archivo</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($tarea = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $tarea['id']; ?></td>
                    <td><?php echo $tarea['estudiante']; ?></td>
                    <td><?php echo basename($tarea['archivo']); ?></td>
                    <td>
                        <a href="<?php echo '../' . $tarea['archivo']; ?>" class="btn btn-primary btn-sm" download>Descargar</a>
                        <a href="Calificar.php?id=<?php echo $tarea['id']; ?>&estudiante=<?php echo urlencode($tarea['estudiante']); ?>" class="btn btn-warning btn-sm">Calificar</a>
                        <a href="Eliminar.php?id=<?php echo $tarea['id']; ?>&archivo=<?php echo urlencode($tarea['archivo']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta tarea?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No se han subido tareas para esta tarea padre.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
