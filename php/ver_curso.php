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
    <title>Material del Curso: <?php echo $curso['titulo']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4"><?php echo $curso['titulo']; ?></h1>

    <div class="accordion" id="accordionExample">
        <?php
        $periodos = ['primer_periodo', 'segundo_periodo', 'tercer_periodo', 'cuarto_periodo'];
        foreach ($periodos as $index => $periodo) {
            echo '<div class="accordion-item">';
            echo '<h2 class="accordion-header" id="heading' . $index . '">';
            echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '" aria-expanded="false" aria-controls="collapse' . $index . '">';
            echo ucfirst(str_replace('_', ' ', $periodo));
            echo '</button>';
            echo '</h2>';
            echo '<div id="collapse' . $index . '" class="accordion-collapse collapse" aria-labelledby="heading' . $index . '" data-bs-parent="#accordionExample">';
            echo '<div class="accordion-body">';

            // Obtener las tareas del curso para el periodo actual
            $tareas = $conexion->query("SELECT * FROM tareas WHERE curso_id = $curso_id AND periodo = '$periodo'");

            if ($tareas->num_rows > 0) {
                while ($tarea = $tareas->fetch_assoc()) {
                    echo '<div class="mb-3">';
                    echo '<p><strong>' . basename($tarea['archivo']) . '</strong> (' . ucfirst($tarea['tipo']) . ')</p>';
                    echo '<a href="' . $tarea['archivo'] . '" class="btn btn-primary" download>Descargar</a>';

                    // Botón para subir una tarea si es del tipo 'tarea'
                    if ($tarea['tipo'] == 'tarea') {
                        echo '<a href="SubirTarea.php?tarea_id=' . $tarea['id'] . '&curso_id=' . $curso_id . '" class="btn btn-success ms-2">Subir Tarea</a>';

                        // Mostrar las tareas subidas por los estudiantes para esta tarea
                        $tareas_alumnos = $conexion->query("SELECT * FROM tareas WHERE tarea_padre_id = " . $tarea['id']);
                        if ($tareas_alumnos->num_rows > 0) {
                            echo '<ul class="mt-3 list-group">';
                            while ($tarea_alumno = $tareas_alumnos->fetch_assoc()) {
                                echo '<li class="list-group-item">';
                                echo '<strong>Estudiante:</strong> ' . $tarea_alumno['estudiante'] . '<br>';
                                echo '<strong>Archivo:</strong> <a href="' . $tarea_alumno['archivo'] . '" download>' . basename($tarea_alumno['archivo']) . '</a><br>';

                                // Mostrar la calificación si ya existe
                                if (!is_null($tarea_alumno['calificacion'])) {
                                    echo '<strong>Calificación:</strong> ' . $tarea_alumno['calificacion'] . '<br>';
                                } else {
                                    echo '<em>No se ha calificado esta tarea aún.</em><br>';
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p class="mt-3"><em>No hay tareas subidas para esta tarea aún.</em></p>';
                        }
                    }

                    echo '</div>';
                }
            } else {
                echo '<p>No hay material disponible para este periodo.</p>';
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
