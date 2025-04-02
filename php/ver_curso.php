<?php
include 'conexion_be.php'; // Conexión a la base de datos

session_start(); // Iniciar sesión

// Obtener el correo del estudiante desde la sesión
$correo_estudiante = $_SESSION['correo'] ?? 'correo_no_definido';

// Verificar si se proporcionó un ID de curso en la URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $curso_id = $_GET['id'];

    // Obtener datos del curso
    $query = "SELECT * FROM cursos WHERE id = $curso_id";
    $result = $conexion->query($query);

    if ($result->num_rows > 0) {
        $curso = $result->fetch_assoc();
    } else {
        echo "<p class='alert alert-danger'>Curso no encontrado.</p>";
        exit;
    }
} else {
    echo "<p class='alert alert-danger'>ID del curso no proporcionado.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso: <?php echo $curso['titulo']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Curso: <?php echo $curso['titulo']; ?></h1>

    <!-- Acordeón para las evaluaciones -->
    <div class="accordion mb-4" id="accordionEvaluaciones">
    <div class="mb-4">
        <a href="CursosEstudiantes.php" class="btn btn-outline-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Volver a Mis Cursos
        </a>
    </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingEvaluaciones">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvaluaciones" aria-expanded="true" aria-controls="collapseEvaluaciones">
                    Evaluaciones del Curso
                </button>
            </h2>
            <div id="collapseEvaluaciones" class="accordion-collapse collapse show" aria-labelledby="headingEvaluaciones" data-bs-parent="#accordionEvaluaciones">
                <div class="accordion-body">
                    <?php
                    // Obtener todas las evaluaciones asociadas al curso
                    $evaluaciones = $conexion->query("SELECT * FROM evaluaciones WHERE curso_id = $curso_id");

                    if ($evaluaciones->num_rows > 0) {
                        while ($eval = $evaluaciones->fetch_assoc()) {
                            echo '<div class="mb-4">';
                            echo '<h5>' . $eval['titulo'] . '</h5>';
                            echo '<p>' . $eval['descripcion'] . '</p>';

                            // Verificar si el estudiante ya ha respondido esta evaluación
                            $respuesta_query = $conexion->query("
                                SELECT * FROM respuestas_estudiantes 
                                WHERE evaluacion_id = " . $eval['id'] . " 
                                AND estudiante_correo = '$correo_estudiante'
                            ");
                            $respuesta = $respuesta_query->fetch_assoc();

                            if ($respuesta) {
                                // Si ya respondió, mostrar "Evaluación Respondida"
                                echo '<button class="btn btn-secondary" disabled>Evaluación Respondida</button>';
                            } else {
                                // Si no ha respondido, mostrar "Ver Evaluación"
                                echo '<a href="ver_evaluacion.php?evaluacion_id=' . $eval['id'] . '&curso_id=' .  $curso_id. '" class="btn btn-primary">Ver Evaluación</a>';
                            }

                            echo '</div>';
                            echo '<hr>'; // Separador entre evaluaciones
                        }
                    } else {
                        echo '<p>No hay evaluaciones disponibles para este curso.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="accordion mb-4" id="accordionProgreso">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingProgreso">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProgreso" aria-expanded="true" aria-controls="collapseProgreso">
                    Progreso del Estudiante
                </button>
            </h2>
            <div id="collapseProgreso" class="accordion-collapse collapse show" aria-labelledby="headingProgreso" data-bs-parent="#accordionProgreso">
                <div class="accordion-body">
                    <p>Haz clic en el siguiente botón para ver tu progreso en este curso.</p>
                    <a href="progreso_estudiantes.php?curso_id=<?php echo $curso_id; ?>&correo=<?php echo urlencode($correo_estudiante); ?>" class="btn btn-primary">
                        Ver Progreso
                    </a>
                </div>
            </div>
        </div>
        </div>

        <div class="accordion mb-4" id="accordionProgreso">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingProgreso">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#collapseProgreso" aria-expanded="true" 
                        aria-controls="collapseProgreso">
                    Progreso y Calificaciones
                </button>
            </h2>
            <div id="collapseProgreso" class="accordion-collapse collapse show" 
                 aria-labelledby="headingProgreso" data-bs-parent="#accordionProgreso">
                <div class="accordion-body">
                    <h5 class="mb-3">Mis Calificaciones</h5>
                    
                    <?php
                    // Consulta para obtener evaluaciones y sus calificaciones
                    $query_evaluaciones = $conexion->prepare("
                        SELECT 
                            e.id, 
                            e.titulo, 
                            e.descripcion,
                            ce.calificacion,
                            ce.respuestas_correctas,
                            ce.respuestas_totales,
                            ce.porcentaje,
                            ce.comentarios,
                            ce.fecha_calificacion,
                            IF(ce.id IS NULL, 0, 1) AS tiene_calificacion
                        FROM evaluaciones e
                        LEFT JOIN calificaciones_evaluaciones ce ON e.id = ce.evaluacion_id 
                            AND ce.estudiante_correo = ?
                        WHERE e.curso_id = ?
                        ORDER BY e.fecha_creacion DESC
                    ");
                    $query_evaluaciones->bind_param("si", $correo_estudiante, $curso_id);
                    $query_evaluaciones->execute();
                    $result_evaluaciones = $query_evaluaciones->get_result();

                    if ($result_evaluaciones->num_rows > 0): 
                        $contador_calificadas = 0;
                        $suma_calificaciones = 0;
                    ?>
                        <div class="list-group">
                            <?php while ($eval = $result_evaluaciones->fetch_assoc()): ?>
                                <div class="list-group-item evaluacion-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($eval['titulo']); ?></h6>
                                        <?php if ($eval['tiene_calificacion']): 
                                            $contador_calificadas++;
                                            $suma_calificaciones += $eval['calificacion'];
                                            
                                            // Determinar color según calificación
                                            $color = 'secondary';
                                            if ($eval['calificacion'] >= 8) $color = 'success';
                                            elseif ($eval['calificacion'] >= 6) $color = 'warning';
                                            elseif ($eval['calificacion'] > 0) $color = 'danger';
                                        ?>
                                            <span class="badge bg-<?php echo $color; ?> nota-badge">
                                                <?php echo number_format($eval['calificacion'], 2); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary nota-badge">Sin calificar</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($eval['descripcion']); ?></p>
                                    
                                    <?php if ($eval['tiene_calificacion']): ?>
                                        <div class="mt-2">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span>
                                                    <?php echo $eval['respuestas_correctas']; ?> / 
                                                    <?php echo $eval['respuestas_totales']; ?> respuestas correctas
                                                </span>
                                                <span><?php echo round($eval['porcentaje']); ?>%</span>
                                            </div>
                                            <div class="progress progress-thin">
                                                <div class="progress-bar bg-<?php echo $color; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $eval['porcentaje']; ?>%">
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($eval['comentarios'])): ?>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#comentario-<?php echo $eval['id']; ?>" 
                                                            aria-expanded="false">
                                                        Ver comentarios
                                                    </button>
                                                    <div class="collapse mt-2" id="comentario-<?php echo $eval['id']; ?>">
                                                        <div class="card card-body small">
                                                            <?php echo nl2br(htmlspecialchars($eval['comentarios'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="text-end small text-muted mt-1">
                                                Calificada el <?php echo date('d/m/Y', strtotime($eval['fecha_calificacion'])); ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2 small text-muted">
                                            Esta evaluación aún no ha sido calificada.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Resumen estadístico -->
                        <?php if ($contador_calificadas > 0): 
                            $promedio = $suma_calificaciones / $contador_calificadas;
                            $color_promedio = 'secondary';
                            if ($promedio >= 8) $color_promedio = 'success';
                            elseif ($promedio >= 6) $color_promedio = 'warning';
                            elseif ($promedio > 0) $color_promedio = 'danger';
                        ?>
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h6 class="card-title">Resumen de Rendimiento</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-<?php echo $color_promedio; ?> fs-6">
                                                        <?php echo number_format($promedio, 2); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Promedio General</div>
                                                    <div class="small text-muted">
                                                        <?php echo $contador_calificadas; ?> evaluaciones calificadas
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small">
                                                <div class="d-flex justify-content-between">
                                                    <span>Evaluaciones completadas:</span>
                                                    <span>
                                                        <?php echo $contador_calificadas; ?> de 
                                                        <?php echo $result_evaluaciones->num_rows; ?>
                                                    </span>
                                                </div>
                                                <div class="progress progress-thin mt-1">
                                                    <div class="progress-bar bg-primary" 
                                                         style="width: <?php echo ($contador_calificadas/$result_evaluaciones->num_rows)*100; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No hay evaluaciones disponibles en este curso.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    </div>

    

    <!-- Acordeón para los periodos -->
    <div class="accordion" id="accordionPeriodos">
        <?php
        // Definir los periodos
        $periodos = ['primer_periodo', 'segundo_periodo', 'tercer_periodo', 'cuarto_periodo'];

        foreach ($periodos as $index => $periodo) {
            echo '<div class="accordion-item">';
            echo '<h2 class="accordion-header" id="heading' . $index . '">';
            echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '" aria-expanded="false" aria-controls="collapse' . $index . '">';
            echo ucfirst(str_replace('_', ' ', $periodo)); // Mostrar el nombre del periodo
            echo '</button>';
            echo '</h2>';
            echo '<div id="collapse' . $index . '" class="accordion-collapse collapse" aria-labelledby="heading' . $index . '" data-bs-parent="#accordionPeriodos">';
            echo '<div class="accordion-body">';

            // Obtener las tareas del periodo actual
            $tareas = $conexion->query("SELECT * FROM tareas WHERE curso_id = $curso_id AND periodo = '$periodo'");

            if ($tareas->num_rows > 0) {
                echo '<table class="table table-bordered">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Tarea</th>';
                echo '<th>Descargar</th>';
                echo '<th>Responder</th>';
                echo '<th>Estado</th>';
                echo '<th>Calificación</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                while ($tarea = $tareas->fetch_assoc()) {
                    

                    echo '<tr>';
                    echo '<td>' . basename($tarea['archivo']) . ' (' . ucfirst($tarea['tipo']) . ')</td>';
                    echo '<td><a href="' . $tarea['archivo'] . '" class="btn btn-primary" download>Descargar</a></td>';

                    // Verificar si el estudiante ya ha respondido la tarea
                    $respuesta_query = $conexion->query("SELECT * FROM respuestas_tareas WHERE tarea_id = " . $tarea['id'] . " AND estudiante_correo = '$correo_estudiante'");
                    $respuesta = $respuesta_query->fetch_assoc();

                    // Botón para responder a la tarea (solo si es del tipo "tarea" y no ha respondido)
                    if ($tarea['tipo'] == 'tarea') {
                        echo '<td>';
                        if ($respuesta) {
                            echo '<button class="btn btn-secondary" disabled>Enviado</button>';
                        } else {
                            echo '<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#responderModal' . $tarea['id'] . '">Responder Tarea</button>';

                            // Modal para responder a la tarea
                            echo '
                            <div class="modal fade" id="responderModal' . $tarea['id'] . '" tabindex="-1" aria-labelledby="responderModalLabel' . $tarea['id'] . '" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="responderModalLabel' . $tarea['id'] . '">Responder a la Tarea</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="guardar_respuesta.php" enctype="multipart/form-data">
                                                <input type="hidden" name="tarea_id" value="' . $tarea['id'] . '">
                                                <input type="hidden" name="estudiante_id" value="' . $correo_estudiante . '">
                                                <input type="hidden" name="id" value="' . $curso_id . '">


                                                <div class="mb-3">
                                                    <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
                                                    <input type="file" name="archivo" id="archivo" class="form-control">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="texto" class="form-label">Escribir Texto (opcional)</label>
                                                    <textarea name="texto" id="texto" rows="3" class="form-control"></textarea>
                                                </div>

                                                <button type="submit" name="subir_respuesta" class="btn btn-primary">Enviar Respuesta</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                        echo '</td>';
                    } else {
                        echo '<td>N/A</td>';
                    }

                    // Estado de la respuesta
                    echo '<td>';
                    if ($respuesta) {
                        echo 'Enviado';
                    } else {
                        echo 'No enviado';
                    }
                    echo '</td>';

                    // Botón para ver la calificación (solo si hay una calificación)
                    if ($respuesta) {
                        $calificacion_query = $conexion->query("SELECT * FROM calificaciones WHERE respuesta_id = " . $respuesta['id']);
                        $calificacion = $calificacion_query->fetch_assoc();

                        if ($calificacion) {
                            echo '<td>';
                            echo '<button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#calificacionModal' . $tarea['id'] . '">Ver Calificación</button>';

                            // Modal para ver la calificación
                            echo '
                            <div class="modal fade" id="calificacionModal' . $tarea['id'] . '" tabindex="-1" aria-labelledby="calificacionModalLabel' . $tarea['id'] . '" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="calificacionModalLabel' . $tarea['id'] . '">Calificación</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Calificación:</strong> ' . $calificacion['calificacion'] . '</p>
                                            <p><strong>Observaciones:</strong> ' . ($calificacion['observaciones'] ?? 'N/A') . '</p>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                            echo '</td>';
                        } else {
                            echo '<td><button class="btn btn-secondary" disabled>Sin calificación</button></td>';
                        }
                    } else {
                        echo '<td><button class="btn btn-secondary" disabled>No respondida</button></td>';
                    }

                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p>No hay tareas disponibles para este periodo.</p>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>