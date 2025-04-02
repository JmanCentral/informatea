<?php
include __DIR__ . '/conexion_be.php';

// Obtener el curso_id desde la URL o de alguna otra fuente
$curso_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : 0;

if ($curso_id <= 0) {
    die("ID de curso no válido.");
}

// Procesar el formulario de calificación si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_calificacion'])) {
    $evaluacion_id = intval($_POST['evaluacion_id']);
    $estudiante_correo = $conexion->real_escape_string($_POST['estudiante_correo']);
    $calificacion = floatval($_POST['calificacion']);
    $respuestas_correctas = intval($_POST['respuestas_correctas']);
    $respuestas_totales = intval($_POST['respuestas_totales']);
    $comentarios = $conexion->real_escape_string($_POST['comentarios'] ?? '');

    // Calcular porcentaje
    $porcentaje = ($respuestas_correctas / $respuestas_totales) * 100;

    // Verificar si ya existe una calificación para actualizar o insertar nueva
    $query_check = $conexion->prepare("SELECT id FROM calificaciones_evaluaciones WHERE evaluacion_id = ? AND estudiante_correo = ?");
    $query_check->bind_param("is", $evaluacion_id, $estudiante_correo);
    $query_check->execute();
    $result_check = $query_check->get_result();

    if ($result_check->num_rows > 0) {
        // Actualizar calificación existente
        $row = $result_check->fetch_assoc();
        $query = $conexion->prepare("UPDATE calificaciones_evaluaciones SET calificacion = ?, respuestas_correctas = ?, respuestas_totales = ?, porcentaje = ?, comentarios = ? WHERE id = ?");
        $query->bind_param("diiisi", $calificacion, $respuestas_correctas, $respuestas_totales, $porcentaje, $comentarios, $row['id']);
    } else {
        // Insertar nueva calificación
        $query = $conexion->prepare("INSERT INTO calificaciones_evaluaciones (evaluacion_id, estudiante_correo, calificacion, respuestas_correctas, respuestas_totales, porcentaje, comentarios) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param("isdiiis", $evaluacion_id, $estudiante_correo, $calificacion, $respuestas_correctas, $respuestas_totales, $porcentaje, $comentarios);
    }

    if ($query->execute()) {
        $mensaje_exito = "Calificación guardada correctamente.";
    } else {
        $mensaje_error = "Error al guardar la calificación: " . $conexion->error;
    }
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
    <title>Evaluaciones y Calificaciones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .respuesta-correcta {
            background-color: #d4edda;
        }
        .respuesta-incorrecta {
            background-color: #f8d7da;
        }
        .badge-calificacion {
            font-size: 0.9rem;
            padding: 0.35em 0.65em;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php if (isset($mensaje_exito)): ?>
        <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_error)): ?>
        <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <h2 class="mb-4">Evaluaciones del Curso</h2>

    <?php if ($result_evaluaciones->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
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
                                <button class="btn btn-info btn-sm btn-ver-respuestas" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#respuestasModal<?php echo $evaluacion['id']; ?>"
                                        data-evaluacion-id="<?php echo $evaluacion['id']; ?>">
                                    Ver Respuestas
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No hay evaluaciones disponibles para este curso.</div>
    <?php endif; ?>
</div>

<!-- Modal para ver respuestas (uno por evaluación) -->
<?php 
$result_evaluaciones->data_seek(0); // Reiniciar el puntero del resultado
while ($evaluacion = $result_evaluaciones->fetch_assoc()): 
?>
<div class="modal fade" id="respuestasModal<?php echo $evaluacion['id']; ?>" tabindex="-1" aria-labelledby="respuestasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="respuestasModalLabel">
                    Respuestas: <?php echo htmlspecialchars($evaluacion['titulo']); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php
                // Obtener estudiantes que respondieron esta evaluación
                $query_estudiantes = $conexion->prepare("
                    SELECT DISTINCT re.estudiante_correo, 
                           IFNULL(ce.calificacion, 'Sin calificar') as calificacion,
                           IFNULL(ce.id, 0) as calificacion_id
                    FROM respuestas_estudiantes re
                    LEFT JOIN calificaciones_evaluaciones ce ON re.evaluacion_id = ce.evaluacion_id 
                                                           AND re.estudiante_correo = ce.estudiante_correo
                    WHERE re.evaluacion_id = ?
                ");
                $query_estudiantes->bind_param("i", $evaluacion['id']);
                $query_estudiantes->execute();
                $result_estudiantes = $query_estudiantes->get_result();

                // Obtener total de preguntas
                $query_total_preguntas = $conexion->prepare("SELECT COUNT(*) as total FROM preguntas_evaluaciones WHERE evaluacion_id = ?");
                $query_total_preguntas->bind_param("i", $evaluacion['id']);
                $query_total_preguntas->execute();
                $total_preguntas = $query_total_preguntas->get_result()->fetch_assoc()['total'];

                if ($result_estudiantes->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Respuestas</th>
                                    <th>Calificación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($estudiante = $result_estudiantes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($estudiante['estudiante_correo']); ?></td>
                                        <td>
                                            <?php
                                            // Obtener respuestas del estudiante
                                            $query_respuestas = $conexion->prepare("
                                                SELECT p.pregunta, r.respuesta, p.respuesta_correcta,
                                                       CASE WHEN r.respuesta = p.respuesta_correcta THEN 1 ELSE 0 END as es_correcta
                                                FROM respuestas_estudiantes r
                                                JOIN preguntas_evaluaciones p ON r.pregunta_id = p.id
                                                WHERE r.evaluacion_id = ? AND r.estudiante_correo = ?
                                            ");
                                            $query_respuestas->bind_param("is", $evaluacion['id'], $estudiante['estudiante_correo']);
                                            $query_respuestas->execute();
                                            $result_respuestas = $query_respuestas->get_result();

                                            $respuestas_correctas = 0;
                                            $respuestas_totales = 0;

                                            if ($result_respuestas->num_rows > 0): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Pregunta</th>
                                                                <th>Respuesta</th>
                                                                <th>Correcta</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php while ($respuesta = $result_respuestas->fetch_assoc()): 
                                                                $respuestas_totales++;
                                                                if ($respuesta['es_correcta']) $respuestas_correctas++;
                                                            ?>
                                                                <tr class="<?php echo $respuesta['es_correcta'] ? 'respuesta-correcta' : 'respuesta-incorrecta'; ?>">
                                                                    <td><?php echo htmlspecialchars($respuesta['pregunta']); ?></td>
                                                                    <td><?php echo strtoupper(htmlspecialchars($respuesta['respuesta'])); ?></td>
                                                                    <td><?php echo strtoupper(htmlspecialchars($respuesta['respuesta_correcta'])); ?></td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p class="mt-2"><strong>Resultado:</strong> <?php echo $respuestas_correctas; ?> de <?php echo $respuestas_totales; ?> correctas (<?php echo round(($respuestas_correctas/$respuestas_totales)*100); ?>%)</p>
                                            <?php else: ?>
                                                <div class="alert alert-warning py-1">No hay respuestas registradas.</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($estudiante['calificacion'] !== 'Sin calificar'): ?>
                                                <span class="badge bg-success badge-calificacion"><?php echo $estudiante['calificacion']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary badge-calificacion">Sin calificar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm <?php echo $estudiante['calificacion_id'] ? 'btn-warning' : 'btn-primary'; ?> btn-calificar"
                                                    data-evaluacion-id="<?php echo $evaluacion['id']; ?>"
                                                    data-estudiante="<?php echo htmlspecialchars($estudiante['estudiante_correo']); ?>"
                                                    data-correctas="<?php echo $respuestas_correctas; ?>"
                                                    data-totales="<?php echo $respuestas_totales; ?>"
                                                    data-calificacion="<?php echo $estudiante['calificacion'] !== 'Sin calificar' ? $estudiante['calificacion'] : ''; ?>">
                                                <?php echo $estudiante['calificacion_id'] ? 'Editar' : 'Calificar'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
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
<?php endwhile; ?>

<!-- Modal global para calificar (reutilizable) -->
<div class="modal fade" id="calificarModalGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Calificar Evaluación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body" id="modal-calificar-body">
                    <!-- Contenido dinámico se insertará aquí via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="guardar_calificacion" class="btn btn-primary">Guardar Calificación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar clic en botones de calificar
    document.querySelectorAll('.btn-calificar').forEach(button => {
        button.addEventListener('click', function() {
            // Obtener datos del botón
            const evaluacionId = this.getAttribute('data-evaluacion-id');
            const estudiante = this.getAttribute('data-estudiante');
            const correctas = parseInt(this.getAttribute('data-correctas'));
            const totales = parseInt(this.getAttribute('data-totales'));
            const calificacionExistente = this.getAttribute('data-calificacion');
            
            // Calcular nota sugerida (sobre 10 puntos)
            const notaSugerida = totales > 0 ? (correctas / totales) * 10 : 0;
            
            // Crear contenido del formulario
            const formContent = `
                <input type="hidden" name="evaluacion_id" value="${evaluacionId}">
                <input type="hidden" name="estudiante_correo" value="${estudiante}">
                <input type="hidden" name="respuestas_correctas" value="${correctas}">
                <input type="hidden" name="respuestas_totales" value="${totales}">
                
                <div class="mb-3">
                    <label class="form-label">Estudiante:</label>
                    <input type="text" class="form-control" value="${estudiante}" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Resultado:</label>
                    <input type="text" class="form-control" value="${correctas} correctas de ${totales} (${Math.round((correctas/totales)*100)}%)" readonly>
                </div>
                
                <div class="mb-3">
                    <label for="calificacion" class="form-label">Calificación (0-10):</label>
                    <input type="number" step="0.01" min="0" max="10" class="form-control" id="calificacion" 
                           name="calificacion" value="${calificacionExistente || notaSugerida.toFixed(2)}" required>
                </div>
                
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios:</label>
                    <textarea class="form-control" id="comentarios" name="comentarios" rows="3" placeholder="Opcional"></textarea>
                </div>
            `;
            
            // Insertar contenido en el modal global
            document.getElementById('modal-calificar-body').innerHTML = formContent;
            
            // Cerrar el modal de respuestas
            const respuestasModal = bootstrap.Modal.getInstance(document.querySelector(`#respuestasModal${evaluacionId}`));
            if (respuestasModal) {
                respuestasModal.hide();
                
                // Mostrar el modal de calificación después de cerrar el de respuestas
                respuestasModal._element.addEventListener('hidden.bs.modal', function() {
                    const calificarModal = new bootstrap.Modal(document.getElementById('calificarModalGlobal'));
                    calificarModal.show();
                }, {once: true});
            } else {
                // Si por alguna razón no se encuentra el modal, mostrar directamente
                const calificarModal = new bootstrap.Modal(document.getElementById('calificarModalGlobal'));
                calificarModal.show();
            }
        });
    });
    
    // Opcional: Validar el formulario antes de enviar
    document.getElementById('calificarModalGlobal')?.addEventListener('shown.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const calificacion = parseFloat(this.elements['calificacion'].value);
                if (isNaN(calificacion)) {
                    alert('La calificación debe ser un número válido');
                    e.preventDefault();
                } else if (calificacion < 0 || calificacion > 10) {
                    alert('La calificación debe estar entre 0 y 10');
                    e.preventDefault();
                }
            });
        }
    });
});
</script>
</body>
</html>