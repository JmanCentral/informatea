<?php
include __DIR__ . '/conexion_be.php'; // Conexión a la base de datos

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
    <style>
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn-sm {
            margin: 2px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Tareas Subidas</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Archivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tarea = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tarea['estudiante']); ?></td>
                            <td><?php echo htmlspecialchars(basename($tarea['archivo'])); ?></td>
                            <td>
                                <a href="<?php echo '../' . $tarea['archivo']; ?>" class="btn btn-primary btn-sm" download>Descargar</a>
                                <a href="Calificar.php?id=<?php echo $tarea['id']; ?>&estudiante=<?php echo urlencode($tarea['estudiante']); ?>" class="btn btn-warning btn-sm">Calificar</a>
                                <a href="Eliminar.php?id=<?php echo $tarea['id']; ?>&archivo=<?php echo urlencode($tarea['archivo']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta tarea?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">No se han subido tareas para este periodo.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>