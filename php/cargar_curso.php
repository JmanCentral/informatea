<?php
include 'conexion_be.php';

if (isset($_GET['id'])) {
    $curso_id = $_GET['id'];

    $stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $curso = $result->fetch_assoc();

    if ($curso) {
        echo '<h2>' . $curso['titulo'] . '</h2>';
        echo '<img class="img-fluid" src="' . $curso['imagen'] . '" alt="Imagen del curso" />';
        echo '<p>' . $curso['descripcion'] . '</p>';

        // Mostrar comentarios del curso
        $comentarios = $conexion->query("SELECT * FROM comentarios WHERE curso_id = $curso_id ORDER BY fecha DESC");

        echo '<h4>Comentarios:</h4>';
        if ($comentarios->num_rows > 0) {
            while ($comentario = $comentarios->fetch_assoc()) {
                echo '<div class="mb-3">';
                echo '<p><strong>' . $comentario['estudiante_correo'] . '</strong> (' . $comentario['fecha'] . ')</p>';
                echo '<p>' . $comentario['comentario'] . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No hay comentarios para este curso.</p>';
        }

        // Formulario para agregar comentario
        echo '<h4>Agregar Comentario:</h4>';
        echo '<form action="insertar_comentario.php" method="POST">';
        echo '  <input type="hidden" name="curso_id" value="' . $curso_id . '">';
        echo '  <textarea class="form-control" name="comentario" rows="3" required></textarea>';
        echo '  <button type="submit" class="btn btn-primary mt-2">Enviar Comentario</button>';
        echo '</form>';
    } else {
        echo '<p>Curso no encontrado.</p>';
    }

    $stmt->close();
    $conexion->close();
}
?>
