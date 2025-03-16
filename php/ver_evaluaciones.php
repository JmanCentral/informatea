<?php
include __DIR__ . '/conexion_be.php';

// Verifica si 'curso_id' está presente en la URL y es un número válido
if (!isset($_GET['curso_id']) || !is_numeric($_GET['curso_id'])) {
    die("ID de curso no válido.");
}

$curso_id = $_GET['curso_id'];

// Obtener las evaluaciones del curso
$query_evaluaciones = $conexion->prepare("
    SELECT id, titulo, descripcion, fecha_creacion 
    FROM evaluaciones 
    WHERE curso_id = ?
");
$query_evaluaciones->bind_param("i", $curso_id);
$query_evaluaciones->execute();
$result_evaluaciones = $query_evaluaciones->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones del Curso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Evaluaciones del Curso</h2>

    <?php if ($result_evaluaciones->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($evaluacion = $result_evaluaciones->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($evaluacion['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($evaluacion['fecha_creacion']); ?></td>
                        <td>
                            <a href="ver_respuestas.php?evaluacion_id=<?php echo $evaluacion['id']; ?>" class="btn btn-info btn-sm">
                                Ver Respuestas
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay evaluaciones disponibles para este curso.</div>
    <?php endif; ?>
</div>
</body>
</html>