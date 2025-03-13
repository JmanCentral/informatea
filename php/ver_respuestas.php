<?php
include __DIR__ . '/conexion_be.php';

// Verifica si 'curso_id' está presente en la URL y es un número válido
if (!isset($_GET['curso_id']) || !is_numeric($_GET['curso_id'])) {
    die("ID de curso no válido.");
}

$curso_id = $_GET['curso_id'];

// Preparar la consulta de manera segura
$query = $conexion->prepare("
    SELECT 
        r.id AS respuesta_id,
        r.estudiante_correo,
        r.pregunta_id,
        p.pregunta,
        r.respuesta,
        p.respuesta_correcta,
        e.id AS evaluacion_id,
        e.titulo AS titulo_evaluacion,
        e.curso_id,
        r.fecha_respuesta
    FROM respuestas_estudiantes r
    JOIN preguntas_evaluaciones p ON r.pregunta_id = p.id
    JOIN evaluaciones e ON p.evaluacion_id = e.id
    WHERE e.curso_id = ?
");

// Ejecutar la consulta
$query->bind_param("i", $curso_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas de Estudiantes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Respuestas de los Estudiantes</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Pregunta</th>
                    <th>Respuesta</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['estudiante_correo']); ?></td>
                        <td><?php echo htmlspecialchars($row['pregunta']); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($row['respuesta'])); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_respuesta']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay respuestas disponibles para este curso.</div>
    <?php endif; ?>
</div>
</body>
</html>