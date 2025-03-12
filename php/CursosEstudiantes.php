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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Cursos</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.5.5/css/simple-line-icons.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css" />
    <link href="../css/styles.css" rel="stylesheet" />
</head>
<body>
<a class="menu-toggle rounded" href="#"><i class="fas fa-bars"></i></a>
<nav id="sidebar-wrapper">
    <ul class="sidebar-nav">
        <li class="sidebar-brand"><a href="#page-top">Informatea</a></li>
        <li class="sidebar-nav-item"><a href="#page-top">Mis Cursos</a></li>
        <li class="sidebar-nav-item"><a href="#contact">Contactanos</a></li>
        <li class="sidebar-nav-item"><a href="login.php">Cerrar Sesion</a></li>
    </ul>
</nav>

<section class="content-section" id="portfolio">
    <div class="container px-4 px-lg-5">
        <div class="content-section-heading text-center">
            <h3 class="text-secondary mb-0">Cursos</h3>
            <h2 class="mb-5">Lo Mejor para Divertirse y Aprender</h2>
        </div>
        <div class="row gx-0">
            <?php
            // Obtener todos los cursos
            $result = $conexion->query("SELECT * FROM cursos");

            while ($curso = $result->fetch_assoc()) {
                echo '<div class="col-lg-6">';
                echo '  <a class="portfolio-item" href="ver_curso.php?id=' . $curso['id'] . '">';
                echo '    <div class="caption">';
                echo '      <div class="caption-content">';
                echo '        <div class="h2">' . $curso['titulo'] . '</div>';
                echo '        <p class="mb-0">' . $curso['descripcion'] . '</p>';
                echo '      </div>';
                echo '    </div>';
                echo '    <img class="img-fluid" src="' . $curso['imagen'] . '" alt="Imagen del curso" />';
                echo '  </a>';
                echo '</div>';
            
                // Obtener los comentarios del curso actual
                $comentarios = $conexion->query("SELECT * FROM comentarios WHERE curso_id = " . $curso['id'] . " ORDER BY fecha DESC");
            
                if ($comentarios->num_rows > 0) {
                    echo '<div class="mt-3">';
                    echo '<h4>Comentarios:</h4>';
                    while ($comentario = $comentarios->fetch_assoc()) {
                        echo '<div class="mb-3">';
                        echo '<p><strong>' . $comentario['estudiante_correo'] . '</strong> (' . $comentario['fecha'] . ')</p>';
                        echo '<p>' . $comentario['comentario'] . '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No hay comentarios para este curso.</p>';
                }
            
                // Formulario para agregar comentarios
                echo '<div class="mt-3">';
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
            }
            

            $conexion->close();
            ?>
        </div>
    </div>
</section>

<footer class="footer text-center">
    <div class="container px-4 px-lg-5">
        <ul class="list-inline mb-5">
            <li class="list-inline-item">
                <a class="social-link rounded-circle text-white mr-3" href="https://sicompuclinic.com/"><i class="icon-social-facebook"></i></a>
            </li>
            <li class="list-inline-item">
                <a class="social-link rounded-circle text-white mr-3" href="https://www.instagram.com/sicompuclinic_/profilecard/?igsh=bHlvYzdueWdyNzBk"><i class="icon-social-twitter"></i></a>
            </li>
            <li class="list-inline-item">
                <a class="social-link rounded-circle text-white" href="#"><i class="icon-social-github"></i></a>
            </li>
        </ul>
        <p class="text-muted small mb-0">Copyright &copy; Sicompuclinic</p>
    </div>
</footer>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/scripts.js"></script>
</body>
</html