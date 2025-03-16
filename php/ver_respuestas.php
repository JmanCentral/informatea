<?php
include __DIR__ . '/conexion_be.php';

// Verifica si 'evaluacion_id' está presente en la URL y es un número válido
if (!isset($_GET['evaluacion_id']) || !is_numeric($_GET['evaluacion_id'])) {
    die("ID de evaluación no válido.");
}

$evaluacion_id = $_GET['evaluacion_id'];

// Obtener la lista de estudiantes que han respondido la evaluación
$query_estudiantes = $conexion->prepare("
    SELECT DISTINCT estudiante_correo 
    FROM respuestas_estudiantes 
    WHERE evaluacion_id = ?
");
$query_estudiantes->bind_param("i", $evaluacion_id);
$query_estudiantes->execute();
$result_estudiantes = $query_estudiantes->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas de los Estudiantes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Respuestas de los Estudiantes</h2>

    <?php if ($result_estudiantes->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($estudiante = $result_estudiantes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($estudiante['estudiante_correo']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#respuestasModal<?php echo $estudiante['estudiante_correo']; ?>">
                                Ver Respuestas
                            </button>

                            <!-- Modal para ver las respuestas del estudiante -->
                            <div class="modal fade" id="respuestasModal<?php echo $estudiante['estudiante_correo']; ?>" tabindex="-1" aria-labelledby="respuestasModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="respuestasModalLabel">
                                                Respuestas de <?php echo htmlspecialchars($estudiante['estudiante_correo']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            // Obtener las respuestas del estudiante para esta evaluación
                                            $query_respuestas = $conexion->prepare("
                                                SELECT 
                                                    p.pregunta,
                                                    r.respuesta,
                                                    p.respuesta_correcta
                                                FROM respuestas_estudiantes r
                                                JOIN preguntas_evaluaciones p ON r.pregunta_id = p.id
                                                WHERE r.evaluacion_id = ? AND r.estudiante_correo = ?
                                            ");
                                            $query_respuestas->bind_param("is", $evaluacion_id, $estudiante['estudiante_correo']);
                                            $query_respuestas->execute();
                                            $result_respuestas = $query_respuestas->get_result();

                                            if ($result_respuestas->num_rows > 0): ?>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Pregunta</th>
                                                            <th>Respuesta del Estudiante</th>
                                                            <th>Respuesta Correcta</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($respuesta = $result_respuestas->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($respuesta['pregunta']); ?></td>
                                                                <td><?php echo strtoupper(htmlspecialchars($respuesta['respuesta'])); ?></td>
                                                                <td><?php echo strtoupper(htmlspecialchars($respuesta['respuesta_correcta'])); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-warning">No hay respuestas disponibles para este estudiante.</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No hay estudiantes que hayan respondido esta evaluación.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>