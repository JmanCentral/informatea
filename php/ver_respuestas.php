<?php
include __DIR__ . '/conexion_be.php';

// Obtener el curso_id desde la URL o de alguna otra fuente
$curso_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : 0;

if ($curso_id <= 0) {
    die("ID de curso no válido.");
}

// Obtener la lista de evaluaciones para el curso específico
$query_evaluaciones = $conexion->prepare("
    SELECT id, titulo, descripcion 
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
    <title>Evaluaciones y Respuestas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Evaluaciones del Curso</h2>

    <?php if ($result_evaluaciones->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Evaluación</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($evaluacion = $result_evaluaciones->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($evaluacion['descripcion']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#respuestasModal<?php echo $evaluacion['id']; ?>">
                                Ver Respuestas
                            </button>

                            <!-- Modal para ver las respuestas de los estudiantes -->
                            <div class="modal fade" id="respuestasModal<?php echo $evaluacion['id']; ?>" tabindex="-1" aria-labelledby="respuestasModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="respuestasModalLabel">
                                                Respuestas de la Evaluación: <?php echo htmlspecialchars($evaluacion['titulo']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            // Obtener la lista de estudiantes que han respondido la evaluación
                                            $query_estudiantes = $conexion->prepare("
                                                SELECT DISTINCT estudiante_correo 
                                                FROM respuestas_estudiantes 
                                                WHERE evaluacion_id = ?
                                            ");
                                            $query_estudiantes->bind_param("i", $evaluacion['id']);
                                            $query_estudiantes->execute();
                                            $result_estudiantes = $query_estudiantes->get_result();

                                            if ($result_estudiantes->num_rows > 0): ?>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Estudiante</th>
                                                            <th>Respuestas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($estudiante = $result_estudiantes->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($estudiante['estudiante_correo']); ?></td>
                                                                <td>
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
                                                                    $query_respuestas->bind_param("is", $evaluacion['id'], $estudiante['estudiante_correo']);
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
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <div class="alert alert-warning">No hay estudiantes que hayan respondido esta evaluación.</div>
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
        <div class="alert alert-warning">No hay evaluaciones disponibles para este curso.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>