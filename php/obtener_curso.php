<?php
include 'conexion_be.php';

if (isset($_GET['id'])) {
    $idCurso = intval($_GET['id']);

    // Obtener el curso seleccionado
    $cursoQuery = $conexion->query("SELECT * FROM cursos WHERE id = $idCurso");
    $curso = $cursoQuery->fetch_assoc();

    if ($curso) {
        echo '<div class="col-lg-12 text-center">';
        echo '  <h2>' . $curso['titulo'] . '</h2>';
        echo '  <p>' . $curso['descripcion'] . '</p>';
        echo '  <img class="img-fluid mb-3" src="' . $curso['imagen'] . '" alt="Imagen del curso" />';
        echo '</div>';

        // Obtener los comentarios del curso
        $comentariosQuery = $conexion->query("SELECT * FROM comentarios WHERE curso_id = $idCurso ORDER BY fecha DESC");

        echo '<div class="col-lg-12">';
        echo '  <h4>Comentarios:</h4>';
        if ($comentariosQuery->num_rows > 0) {
            while ($comentario = $comentariosQuery->fetch_assoc()) {
                echo '<div class="mb-3">';
                echo '  <p><strong>' . $comentario['estudiante_correo'] . '</strong> (' . $comentario['fecha'] . ')</p>';
                echo '  <p>' . $comentario['comentario'] . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No hay comentarios para este curso.</p>';
        }

        // Formulario para agregar comentarios
        echo '<h4>Agregar Comentario:</h4>';
        echo '<form action="insertar_comentario.php" method="POST">';
        echo '  <input type="hidden" name="curso_id" value="' . $curso['id'] . '">';
        echo '  <div class="form-group">';
        echo '    <label for="comentario">Comentario:</label>';
        echo '    <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>';
        echo '  </div>';
        echo '  <button type="submit" class="btn btn-primary">Enviar Comentario</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<p>Error: No se encontró el curso.</p>';
    }

    $conexion->close();
}
?>
