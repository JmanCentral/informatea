
<?php
include 'conexion_be.php'; // Conexión a la base de datos

// Procesar la creación del curso con imagen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_curso'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $imagen = $_FILES['imagen']['name'];
    $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $titulo);
    $ruta_imagen = $nombre_carpeta . '/' . $imagen;

    // Crear la carpeta del curso si no existe
    if (!is_dir($nombre_carpeta)) {
        mkdir($nombre_carpeta, 0777, true);
    }

    // Mover la imagen del curso a la carpeta creada
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen)) {
        $sql = "INSERT INTO cursos (titulo, descripcion, imagen) VALUES ('$titulo', '$descripcion', '$ruta_imagen')";
        if ($conexion->query($sql) === TRUE) {
            $id_curso = $conexion->insert_id;
            $archivo_curso = crearEstructuraCurso($titulo, $id_curso);
            echo "<p>Curso creado: <a href='$archivo_curso'>$archivo_curso</a></p>";
        } else {
            echo "<p>Error al guardar los datos del curso en la base de datos.</p>";
        }
    } else {
        echo "<p>Error al subir la imagen del curso.</p>";
    }
}

// Función para eliminar una carpeta y su contenido recursivamente
function eliminarCarpeta($carpeta) {
    if (is_dir($carpeta)) {
        $archivos = array_diff(scandir($carpeta), ['.', '..']);
        foreach ($archivos as $archivo) {
            $ruta = $carpeta . DIRECTORY_SEPARATOR . $archivo;
            is_dir($ruta) ? eliminarCarpeta($ruta) : unlink($ruta);
        }
        return rmdir($carpeta);
    }
    return false;
}

// Eliminar curso y su carpeta si se solicita
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    // Obtener el título del curso para eliminar su carpeta y archivo PHP
    $result = $conexion->query("SELECT titulo FROM cursos WHERE id = $id");
    if ($curso = $result->fetch_assoc()) {
        $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $curso['titulo']);
        $archivo_php = $nombre_carpeta . "/Curso_" . str_replace(' ', '_', $curso['titulo']) . ".php";

        // Verificar y eliminar el archivo PHP del curso si existe
        if (file_exists($archivo_php) && !unlink($archivo_php)) {
            echo '<p class="alert alert-danger">No se pudo eliminar el archivo PHP del curso.</p>';
            exit; // Detener la ejecución si no se puede eliminar el archivo PHP
        }

        // Eliminar la carpeta del curso y su contenido
        if (eliminarCarpeta($nombre_carpeta)) {
            // Eliminar el curso de la base de datos
            if ($conexion->query("DELETE FROM cursos WHERE id = $id") === TRUE) {
                echo '<p class="alert alert-success">Curso eliminado correctamente.</p>';
            } else {
                echo '<p class="alert alert-danger">Error al eliminar el curso de la base de datos.</p>';
            }
        } else {
            echo '<p class="alert alert-danger">No se pudo eliminar la carpeta del curso.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Curso no encontrado.</p>';
    }
}

function crearEstructuraCurso($titulo, $id_curso) {
    // Crear la carpeta del curso si no existe
    $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $titulo);
    if (!is_dir($nombre_carpeta)) {
        mkdir($nombre_carpeta, 0777, true);
    }

    // Nombre del archivo PHP del curso
    $nombre_archivo = $nombre_carpeta . "/Curso_" . str_replace(' ', '_', $titulo) . ".php";

    // Contenido dinámico del archivo PHP
    $contenido = "
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    \$curso_id = $id_curso;
    \$titulo = '$titulo';

    // Función para listar respuestas de estudiantes por periodo
    function listarRespuestas(\$periodo) {
        global \$conexion, \$curso_id;

        // Obtener las tareas del periodo
        \$tareas = \$conexion->query(\"SELECT id FROM tareas WHERE curso_id = \$curso_id AND periodo = '\$periodo'\");

        if (\$tareas->num_rows > 0) {
            echo '<table class=\"table table-bordered\">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Estudiante</th>';
            echo '<th>Archivo</th>';
            echo '<th>Texto</th>';
            echo '<th>Calificación</th>';
            echo '<th>Acciones</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while (\$tarea = \$tareas->fetch_assoc()) {
                // Obtener las respuestas de los estudiantes para esta tarea
                \$respuestas = \$conexion->query(\"SELECT * FROM respuestas_tareas WHERE tarea_id = \" . \$tarea['id']);

                if (\$respuestas->num_rows > 0) {
                    while (\$respuesta = \$respuestas->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . \$respuesta['estudiante_correo'] . '</td>';
                        echo '<td>';
                        if (!empty(\$respuesta['archivo'])) {

                            \$ruta_archivo = 'Respuestas/' . basename(\$respuesta['archivo']);
                            echo '<a href=\"' . \$ruta_archivo . '\" download>' . basename(\$respuesta['archivo']) . '</a>';
                        } else {
                            echo 'N/A';
                        }
                        echo '</td>';
                        echo '<td>' . (\$respuesta['texto'] ?? 'N/A') . '</td>';

                        
                        // Verificar si la respuesta ya tiene una calificación
                        \$calificacion_query = \$conexion->query(\"SELECT * FROM calificaciones WHERE respuesta_id = \" . \$respuesta['id']);
                        \$calificacion = \$calificacion_query->fetch_assoc();

                        if (\$calificacion) {
                        echo '<td>' . \$calificacion['calificacion'] . '</td>';
                        echo '<td><button class=\"btn btn-secondary btn-sm \" disabled><i class= \"bi bi-pencil-square \"> </i> Calificado</button></td>';
                        } else {
                        echo '<td>Sin calificar</td>';
                        echo '<td>';
                        echo '<button class=\"btn btn-info btn-sm\" data-bs-toggle=\"modal\" data-bs-target=\"#calificarModal' . \$respuesta['id'] . '\">
                                <i class=\"bi bi-pencil-square\"></i> Calificar
                              </button>';
                        
                        echo '
                        <div class=\"modal fade\" id=\"calificarModal' . \$respuesta['id'] . '\" tabindex=\"-1\" aria-labelledby=\"calificarModalLabel' . \$respuesta['id'] . '\" aria-hidden=\"true\">
                            <div class=\"modal-dialog\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\" id=\"calificarModalLabel' . \$respuesta['id'] . '\">Calificar Respuesta</h5>
                                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                                    </div>
                                    <div class=\"modal-body\">
                                        <form method=\"POST\" action=\"/../../../php/guardar_calificacion.php\">
                                            <input type=\"hidden\" name=\"respuesta_id\" value=\"' . \$respuesta['id'] . '\">
                                            <div class=\"mb-3\">
                                                <label for=\"calificacion\" class=\"form-label\">Calificación</label>
                                                <input type=\"number\" name=\"calificacion\" id=\"calificacion\" class=\"form-control\" min=\"0\" max=\"10\" step=\"0.1\" required>
                                            </div>
                                            <div class=\"mb-3\">
                                                <label for=\"observaciones\" class=\"form-label\">Observaciones</label>
                                                 <textarea name=\"observaciones\" id=\"observaciones\" class=\"form-control\" rows=\"3\"></textarea>
                                            </div>
                                            <button type=\"submit\" name=\"guardar_calificacion\" class=\"btn btn-primary\">Guardar Calificación</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan=\"5\">No hay respuestas subidas por los estudiantes.</td></tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p class=\"text-muted\">No hay tareas en este periodo.</p>';
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang=\"es\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Curso <?php echo \$titulo; ?></title>
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
        <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css\" rel=\"stylesheet\">
        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
            }
            .sidebar {
                width: 250px;
                height: 100vh;
                background-color: #343a40;
                color: white;
                position: fixed;
                top: 0;
                left: -250px;
                overflow-y: auto;
                padding-top: 20px;
                transition: left 0.3s;
            }
            .sidebar.active {
                left: 0;
            }
            .sidebar a {
                color: white;
                padding: 10px 15px;
                text-decoration: none;
                display: block;
            }
            .sidebar a:hover {
                background-color: #495057;
            }
            .content {
                margin-left: 0;
                padding: 20px;
                transition: margin-left 0.3s;
                margin-top: 60px;
            }
            .content.active {
                margin-left: 250px;
            }
            .navbar {
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                display: flex;
                align-items: center;
                height: 60px;
            }
            .navbar button {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
            }
            .logout {
                margin-left: auto;
                cursor: pointer;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <!-- Menú lateral -->
        <div class=\"sidebar\" id=\"sidebar\">
            <h4 class=\"text-center mb-4\">Menú</h4>
            <a href=\"../../../php/crear_evaluacion.php?curso_id=<?php echo \$curso_id; ?>\">Crear Evaluación</a>
            <a href=\"../../../php/ver_respuestas.php?curso_id=<?php echo \$curso_id; ?>\">Consultar Evaluación</a>
            <a href=\"../../../php/ver_comentarios.php?curso_id=<?php echo \$curso_id; ?>\">Ver Comentarios</a>
            <a href=\"../../../php/agregar_tarea.php?curso_id=<?php echo \$curso_id; ?>\">Ver Tareas</a>
            <a href=\"#\" onclick=\"mostrarPeriodos()\">Ver Periodos</a>
            <a href=\"../../../php/profesor.php\">Volver a Cursos</a> <!-- Nuevo enlace para volver a los cursos generales -->
            <a href=\"../../../php/logout.php\" class=\"logout\">Cerrar Sesión</a>
        </div>

        <!-- Barra superior -->
        <div class=\"navbar\">
            <button onclick=\"toggleSidebar()\">☰</button>
            <h4 class=\"ms-3\">Curso: <?php echo \$titulo; ?></h4>
        </div>

        <!-- Contenido principal -->
        <div class=\"content\" id=\"content\">
            <!-- Acordeón para los períodos -->
            <div class=\"accordion\" id=\"accordionExample\">
                <div class=\"accordion-item\">
                    <h2 class=\"accordion-header\">
                        <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseOne\">
                            Primer Periodo
                        </button>
                    </h2>
                    <div id=\"collapseOne\" class=\"accordion-collapse collapse show\">
                        <div class=\"accordion-body\">
                            <?php listarRespuestas('primer_periodo'); ?>
                        </div>
                    </div>
                </div>
                <div class=\"accordion-item\">
                    <h2 class=\"accordion-header\">
                        <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseTwo\">
                            Segundo Periodo
                        </button>
                    </h2>
                    <div id=\"collapseTwo\" class=\"accordion-collapse collapse show\">
                        <div class=\"accordion-body\">
                            <?php listarRespuestas('segundo_periodo'); ?>
                        </div>
                    </div>
                </div>
                <div class=\"accordion-item\">
                    <h2 class=\"accordion-header\">
                        <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseThree\">
                            Tercer Periodo
                        </button>
                    </h2>
                    <div id=\"collapseThree\" class=\"accordion-collapse collapse show\">
                        <div class=\"accordion-body\">
                            <?php listarRespuestas('tercer_periodo'); ?>
                        </div>
                    </div>
                </div>
                <div class=\"accordion-item\">
                    <h2 class=\"accordion-header\">
                        <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseFour\">
                            Cuarto Periodo
                        </button>
                    </h2>
                    <div id=\"collapseFour\" class=\"accordion-collapse collapse show\">
                        <div class=\"accordion-body\">
                            <?php listarRespuestas('cuarto_periodo'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Función para mostrar/ocultar el menú lateral
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            }

            // Función para mostrar los periodos
            function mostrarPeriodos() {
                alert('Mostrando periodos...');
            }
        </script>
        <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
    </body>
    </html>
    ";

    // Escribir el contenido en el archivo PHP
    file_put_contents($nombre_archivo, $contenido);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: -250px;
            overflow-y: auto;
            padding-top: 20px;
            transition: left 0.3s;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s;
            margin-top: 60px; /* Añade este margen superior */
        }
        .content.active {
            margin-left: 250px;
        }
        .navbar {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            height: 60px; /* Asegúrate de que la altura sea consistente */
        }
        .navbar button {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .card {
            flex: 1 1 calc(50% - 20px); /* Dos columnas con espacio entre ellas */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card img {
            max-width: 100%; /* La imagen no superará el ancho de la tarjeta */
            height: 200px; /* Altura fija para todas las imágenes */
            object-fit: cover; /* Ajusta la imagen manteniendo la proporción */
            border-radius: 10px 10px 0 0; /* Bordes redondeados solo en la parte superior */
        }
        .logout {
            margin-left: auto;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <div class="sidebar" id="sidebar">
        <h4 class="text-center mb-4">Menú</h4>
        <a href="#" onclick="mostrarFormulario()">Registrar Curso</a>
        <a href="#" onclick="mostrarCursos()">Ver Cursos</a>
        <a href="#" class="logout" onclick="confirmarCerrarSesion()">Cerrar Sesión</a>
    </div>

    <!-- Barra superior -->
    <div class="navbar">
        <button onclick="toggleSidebar()">☰</button>
        <h4 class="ms-3">Dashboard de Cursos</h4>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="content">
        <!-- Formulario para agregar un nuevo curso (oculto por defecto) -->
        <div id="formularioCurso" class="card mb-4" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Agregar Nuevo Curso</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título del Curso</label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Curso</label>
                        <input type="file" name="imagen" id="imagen" class="form-control" required>
                    </div>
                    <button type="submit" name="agregar_curso" class="btn btn-success w-100">Agregar Curso</button>
                </form>
            </div>
        </div>

        <!-- Lista de cursos en dos columnas -->
        <div class="card-container" id="listaCursos">
            <?php
            // Conexión a la base de datos (asegúrate de que $conexion esté definido)
            $result = $conexion->query("SELECT * FROM cursos");
            if ($result->num_rows > 0) {
                while ($curso = $result->fetch_assoc()) {
                    $archivo_curso = 'Cursos/' . str_replace(' ', '_', $curso['titulo']) . '/Curso_' . str_replace(' ', '_', $curso['titulo']) . '.php';
                    echo '<div class="card">';
                    echo '<div class="card-body">';
                    echo '<img src="' . $curso['imagen'] . '" alt="Imagen del curso">';
                    echo '<h5 class="card-title">' . $curso['titulo'] . '</h5>';
                    echo '<p class="card-text">' . $curso['descripcion'] . '</p>';
                    echo '<button onclick="mostrarCursoDestacado(\'' . $curso['titulo'] . '\', \'' . $curso['descripcion'] . '\', \'' . $curso['imagen'] . '\')" class="btn btn-info btn-custom">Ver Detalles</button> ';
                    echo '<a href="' . $archivo_curso . '" class="btn btn-primary btn-custom">Ver Curso Completo</a> ';
                    echo '<a href="?eliminar=' . $curso['id'] . '" class="btn btn-danger btn-custom">Eliminar Curso</a> ';
                    echo '<a href="editar_curso.php?id=' . $curso['id'] . '" class="btn btn-warning btn-custom">Editar Curso</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="alert alert-info text-center">No hay cursos disponibles.</div>';
            }
            ?>
        </div>
    </div>

    <script src="../js/confirmDialog.js"></script>

    <script>
        // Función para mostrar/ocultar el menú lateral
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('active');
        }

        // Función para cerrar sesión
        function confirmarCerrarSesion() {
            showConfirmDialog(
                "¿Estás seguro de que quieres cerrar sesión?",
                function () {
                    // Si el usuario confirma, redirigir a logout.php
                    window.location.href = 'logout.php';
                },
                function () {
                    // Si el usuario cancela, no hacer nada
                    console.log("Cierre de sesión cancelado.");
                }
            );
        }

        // Función para mostrar el formulario de registro
        function mostrarFormulario() {
            document.getElementById('formularioCurso').style.display = 'block';
            document.getElementById('listaCursos').style.display = 'none';
        }

        // Función para mostrar la lista de cursos
        function mostrarCursos() {
            document.getElementById('formularioCurso').style.display = 'none';
            document.getElementById('listaCursos').style.display = 'flex';
        }

        // Función para mostrar la vista destacada del curso
        function mostrarCursoDestacado(titulo, descripcion, imagen) {
            alert(`Curso: ${titulo}\nDescripción: ${descripcion}\nImagen: ${imagen}`);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>