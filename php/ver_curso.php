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
                while ($tarea = $tareas->fetch_assoc()) {
                    echo '<div class="mb-3">';
                    echo '<p><strong>' . basename($tarea['archivo']) . '</strong> (' . ucfirst($tarea['tipo']) . ')</p>';
                    echo '<a href="' . $tarea['archivo'] . '" class="btn btn-primary" download>Descargar</a>';

                    // Botón para responder a la tarea
                    if ($tarea['tipo'] == 'tarea') {
                        echo '<button class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#responderModal' . $tarea['id'] . '">Responder Tarea</button>';

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
                                            <input type="hidden" name="estudiante_id" value="' . $_SESSION['correo'] . '">

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

                    echo '</div>';
                }
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