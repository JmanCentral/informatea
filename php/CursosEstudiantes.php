<?php
session_start();
include 'conexion_be.php'; // Conexión a la base de datos

// Verifica que el rol sea 2 para mostrar la opción
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2) {
    echo '<li><a href="CursosEstudiantes.php">Cursos Estudiantes</a></li>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cursos</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .curso-izquierda {
            float: left;
            width: 48%;
            margin-right: 2%;
        }
        .curso-derecha {
            float: right;
            width: 48%;
            margin-left: 2%;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Informatea</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Mis Cursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contáctanos</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="login.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <section class="content-section" id="portfolio">
        <div class="container px-4 px-lg-5">
            <div class="content-section-heading text-center">
                <h2 class="text-secondary mb-0">Cursos</h2>
                <h3 class="mb-5">Lo Mejor para Divertirse y Aprender</h3>
            </div>
            <div class="row gx-0 clearfix">
                <?php
                // Obtener todos los cursos
                $result = $conexion->query("SELECT * FROM cursos");

                $toggle = true; // Variable para alternar entre izquierda y derecha

                while ($curso = $result->fetch_assoc()) {
                    $class = $toggle ? 'curso-izquierda' : 'curso-derecha';
                    $toggle = !$toggle; // Alternar el valor de toggle

                    echo '<div class="' . $class . '">';
                    echo '  <div class="card shadow-sm">';
                    echo '    <img class="card-img-top" src="' . $curso['imagen'] . '" alt="Imagen del curso">';
                    echo '    <div class="card-body">';
                    echo '      <h3 class="card-title">' . $curso['titulo'] . '</h3>';
                    echo '      <p class="card-text">' . $curso['descripcion'] . '</p>';
                    echo '      <div class="d-flex justify-content-between align-items-center">';
                    echo '        <a href="ver_curso.php?id=' . $curso['id'] . '" class="btn btn-primary">Ver Curso</a>';
                    echo '        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalComentario' . $curso['id'] . '">Agregar Comentario</button>';
                    echo '        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalVerComentarios' . $curso['id'] . '">Ver Comentarios</button>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';

                    // Modal para agregar comentarios
                    echo '<div class="modal fade" id="modalComentario' . $curso['id'] . '" tabindex="-1" aria-labelledby="modalComentarioLabel' . $curso['id'] . '" aria-hidden="true">';
                    echo '  <div class="modal-dialog">';
                    echo '    <div class="modal-content">';
                    echo '      <div class="modal-header">';
                    echo '        <h5 class="modal-title" id="modalComentarioLabel' . $curso['id'] . '">Agregar Comentario</h5>';
                    echo '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                    echo '      </div>';
                    echo '      <div class="modal-body">';
                    echo '        <form id="formComentario' . $curso['id'] . '" class="form-comentario">';
                    echo '          <input type="hidden" name="curso_id" value="' . $curso['id'] . '">';
                    echo '          <div class="form-group">';
                    echo '            <label for="comentario">Comentario:</label>';
                    echo '            <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>';
                    echo '          </div>';
                    echo '          <button type="submit" class="btn btn-primary mt-3">Guardar Comentario</button>';
                    echo '        </form>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';

                    // Modal para ver comentarios
                    echo '<div class="modal fade" id="modalVerComentarios' . $curso['id'] . '" tabindex="-1" aria-labelledby="modalVerComentariosLabel' . $curso['id'] . '" aria-hidden="true">';
                    echo '  <div class="modal-dialog modal-lg">';
                    echo '    <div class="modal-content">';
                    echo '      <div class="modal-header">';
                    echo '        <h5 class="modal-title" id="modalVerComentariosLabel' . $curso['id'] . '">Comentarios del Curso: ' . $curso['titulo'] . '</h5>';
                    echo '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                    echo '      </div>';
                    echo '      <div class="modal-body">';
                    // Obtener comentarios
                    $comentarios = $conexion->query("SELECT * FROM comentarios WHERE curso_id = " . $curso['id'] . " ORDER BY fecha DESC");
                    if ($comentarios->num_rows > 0) {
                        while ($comentario = $comentarios->fetch_assoc()) {
                            echo '<div class="border-bottom pb-2 mb-2">';
                            echo '<p><strong>' . $comentario['estudiante_correo'] . '</strong> (' . $comentario['fecha'] . ')</p>';
                            echo '<p>' . $comentario['comentario'] . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-muted">No hay comentarios para este curso.</p>';
                    }
                    echo '      </div>';
                    echo '      <div class="modal-footer">';
                    echo '        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
                $conexion->close();
                ?>
            </div>
        </div>
    </section>
    
    <footer class="footer bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-1">&copy; 2025 Informatea - Todos los derechos reservados.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-white"><i class="fab fa-github fa-lg"></i></a>
            </div>
        </div>
    </footer>
    
    <!-- Scripts de Bootstrap y jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script para manejar el envío de comentarios con AJAX -->
    <script>
        $(document).ready(function() {
            // Manejar el envío de comentarios
            $('.form-comentario').on('submit', function(e) {
                e.preventDefault(); // Evita que el formulario se envíe de forma tradicional

                var form = $(this);
                var url = 'insertar_comentario.php';
                var formData = form.serialize(); // Serializa los datos del formulario

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function(response) {
                        // Cierra el modal
                        form.closest('.modal').modal('hide');
                        // Recarga la página para mostrar el nuevo comentario
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error al agregar el comentario. Inténtalo de nuevo.');
                    }
                });
            });
        });
    </script>
</body>
</html>