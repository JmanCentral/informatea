
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

// Función para crear la estructura del curso
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

    // Función para listar archivos y agregar la opción de eliminar
    function listarArchivos(\$periodo) {
        global \$conexion, \$curso_id;
        \$result = \$conexion->query(\"SELECT * FROM tareas WHERE curso_id=\$curso_id AND periodo='\$periodo'\");
        echo '<div class=\"list-group\">';
        while (\$archivo = \$result->fetch_assoc()) {
            \$archivo_path = \$archivo['archivo'];
            echo '<div class=\"list-group-item d-flex justify-content-between align-items-center\">';
            echo '<div>';
            echo '<p><strong>' . basename(\$archivo_path) . '</strong> - ' . ucfirst(\$archivo['tipo']) . '</p>';
            echo '</div>';
            echo '<div>';
            echo '<a href=\"' . \$archivo_path . '\" class=\"btn btn-primary btn-sm me-2\" download>Descargar</a>';
            echo '<a href=\"../../../php/TareasSubidas.php?periodo=' . \$archivo['periodo'] . '&tarea_id=' . \$archivo['id'] . '\" class=\"btn btn-warning btn-sm me-2\">Ver Tareas Subidas</a>';
            echo '<a href=\"../../../php/EliminarTarea.php?id=' . \$archivo['id'] . '&archivo=' . urlencode(\$archivo['archivo']) . '\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm(\'¿Estás seguro de eliminar esta tarea?\');\">Eliminar</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST['subir_archivo'])) {
        \$periodo = \$_POST['periodo'];
        \$tipo = \$_POST['tipo'];
        \$texto = \$_POST['texto'] ?? '';
        \$archivo = \$_FILES['archivo']['name'] ?? '';
        \$ruta = __DIR__ . '/' . \$archivo;

        if (!empty(\$archivo) && move_uploaded_file(\$_FILES['archivo']['tmp_name'], \$ruta)) {
            \$ruta_relativa = 'Cursos/' . basename(__DIR__) . '/' . \$archivo;
            \$conexion->query(\"INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES (\$curso_id, '\$periodo', '\$ruta_relativa', '\$tipo')\");
            echo '<p class=\"alert alert-success\">Archivo subido correctamente.</p>';
        } elseif (!empty(\$texto)) {
            \$conexion->query(\"INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES (\$curso_id, '\$periodo', '\$texto', 'texto')\");
            echo '<p class=\"alert alert-success\">Texto guardado correctamente.</p>';
        } else {
            echo '<p class=\"alert alert-danger\">Error al subir el archivo o enviar el texto.</p>';
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
        <style>
            body {
                background-color: #f8f9fa;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .btn-custom {
                margin: 5px;
            }
            .accordion-button {
                background-color: #007bff;
                color: white;
                font-weight: bold;
            }
            .accordion-button:not(.collapsed) {
                background-color: #0056b3;
                color: white;
            }
            .accordion-body {
                background-color: #ffffff;
                border: 1px solid #dee2e6;
                border-top: none;
            }
            .list-group-item {
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
                border-radius: 5px;
            }
            .list-group-item:hover {
                background-color: #f1f1f1;
            }
        </style>
    </head>
    <body>
    <div class=\"container mt-5\">
        <h1 class=\"text-center mb-4\">Curso: <?php echo \$titulo; ?></h1>

        <!-- Botones de acción -->
        <div class=\"text-center mb-4\">
            <a href=\"../../../php/crear_evaluacion.php?curso_id=<?php echo \$curso_id; ?>\" class=\"btn btn-success btn-custom\">Crear Evaluación</a>
            <a href=\"../../../php/ver_respuestas.php?curso_id=<?php echo \$curso_id; ?>\" class=\"btn btn-success btn-custom\">Consultar Evaluación</a>
            <a href=\"../../../php/ver_comentarios.php?curso_id=<?php echo \$curso_id; ?>\" class=\"btn btn-info btn-custom\">Ver Comentarios</a>
        </div>

        <!-- Formulario para subir archivos o enviar texto -->
        <form method=\"POST\" enctype=\"multipart/form-data\" class=\"mb-4\">
            <div class=\"row g-3\">
                <div class=\"col-md-6\">
                    <label for=\"periodo\" class=\"form-label\">Periodo</label>
                    <select name=\"periodo\" id=\"periodo\" class=\"form-select\">
                        <option value=\"primer_periodo\">Primer Periodo</option>
                        <option value=\"segundo_periodo\">Segundo Periodo</option>
                        <option value=\"tercer_periodo\">Tercer Periodo</option>
                        <option value=\"cuarto_periodo\">Cuarto Periodo</option>
                    </select>
                </div>
                <div class=\"col-md-6\">
                    <label for=\"tipo\" class=\"form-label\">Tipo</label>
                    <select name=\"tipo\" id=\"tipo\" class=\"form-select\">
                        <option value=\"material\">Material</option>
                        <option value=\"tarea\">Tarea</option>
                    </select>
                </div>
            </div>
            <div class=\"mb-3 mt-3\">
                <label for=\"texto\" class=\"form-label\">Escribir Texto (opcional)</label>
                <textarea name=\"texto\" id=\"texto\" rows=\"3\" class=\"form-control\"></textarea>
            </div>
            <div class=\"mb-3\">
                <label for=\"archivo\" class=\"form-label\">Subir Archivo (opcional)</label>
                <input type=\"file\" name=\"archivo\" id=\"archivo\" class=\"form-control\">
            </div>
            <button type=\"submit\" name=\"subir_archivo\" class=\"btn btn-primary w-100\">Subir Archivo o Enviar Texto</button>
        </form>

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
                        <?php listarArchivos('primer_periodo'); ?>
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
                        <?php listarArchivos('segundo_periodo'); ?>
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
                        <?php listarArchivos('tercer_periodo'); ?>
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
                        <?php listarArchivos('cuarto_periodo'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
    </body>
    </html>
    ";

    // Guardar el contenido en el archivo
    file_put_contents($nombre_archivo, $contenido);

    return $nombre_archivo;
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