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