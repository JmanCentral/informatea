
<?php
include 'conexion_be.php'; // Conexión a la base de datos



// **Función para generar el archivo EliminarTarea.php**
function crearEliminarTarea($carpeta) {
    $contenido = <<<PHP
    <?php
    include __DIR__ . '/../../conexion_be.php';
    \$tarea_id = \$_GET['id'] ?? '';
    \$archivo = \$_GET['archivo'] ?? '';

    if (empty(\$tarea_id)) {
        die('<p class="alert alert-danger">ID de tarea no proporcionado.</p>');
    }

    if (file_exists(\$archivo)) {
        unlink(\$archivo);
    }

    \$query = "DELETE FROM tareas WHERE id = \$tarea_id";
    if (\$conexion->query(\$query) === TRUE) {
        echo '<p class="alert alert-success">Tarea eliminada correctamente.</p>';
    } else {
        echo '<p class="alert alert-danger">Error al eliminar la tarea.</p>';
    }

    header('Location: ' . \$_SERVER['HTTP_REFERER']);
    exit;
    ?>
    PHP;

    file_put_contents("$carpeta/EliminarTarea.php", $contenido);
}

// **Función para generar el archivo TareasSubidas.php**
function crearTareasSubidas($carpeta) {
    $contenido = <<<PHP
    <?php
    include __DIR__ . '/../../conexion_be.php';
    \$tarea_padre_id = \$_GET['tarea_id'] ?? '';

    if (empty(\$tarea_padre_id)) {
        echo '<p class="alert alert-danger">No se ha especificado la tarea padre correctamente.</p>';
        exit;
    }

    \$query = "SELECT * FROM tareas WHERE tarea_padre_id = \$tarea_padre_id";
    \$result = \$conexion->query(\$query);
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Tareas Subidas</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Tareas Subidas para la Tarea Padre ID: <?php echo \$tarea_padre_id; ?></h1>

        <?php if (\$result && \$result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Tarea ID</th>
                    <th>Estudiante</th>
                    <th>Archivo</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php while (\$tarea = \$result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo \$tarea['id']; ?></td>
                        <td><?php echo \$tarea['estudiante']; ?></td>
                        <td><?php echo basename(\$tarea['archivo']); ?></td>
                        <td>
                            <a href="<?php echo '../' . \$tarea['archivo']; ?>" class="btn btn-primary btn-sm" download>Descargar</a>
                            <a href="Calificar.php?id=<?php echo \$tarea['id']; ?>&estudiante=<?php echo urlencode(\$tarea['estudiante']); ?>" class="btn btn-warning btn-sm">Calificar</a>
                            <a href="EliminarTarea.php?id=<?php echo \$tarea['id']; ?>&archivo=<?php echo urlencode(\$tarea['archivo']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta tarea?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No se han subido tareas para esta tarea padre.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    PHP;

    file_put_contents("$carpeta/TareasSubidas.php", $contenido);
}

// **Función para generar el archivo Calificar.php**
function crearCalificar($carpeta) {
    $contenido = <<<PHP
    <?php
    include __DIR__ . '/../../conexion_be.php';
    \$tarea_id = \$_GET['id'] ?? '';
    \$estudiante = \$_GET['estudiante'] ?? '';

    if (empty(\$tarea_id)) {
        die('<p class="alert alert-danger">No se ha proporcionado un ID de tarea válido.</p>');
    }

    if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
        \$calificacion = \$_POST['calificacion'];
        \$query = "UPDATE tareas SET calificacion = '\$calificacion' WHERE id = \$tarea_id";
        if (\$conexion->query(\$query) === TRUE) {
            echo '<p class="alert alert-success">Calificación guardada correctamente.</p>';
        } else {
            echo '<p class="alert alert-danger">Error al guardar la calificación.</p>';
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Calificar Tarea</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container mt-5">
        <h1>Calificar Tarea de: <?php echo htmlspecialchars(\$estudiante); ?></h1>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label for="calificacion" class="form-label">Calificación (0-100)</label>
                <input type="number" name="calificacion" id="calificacion" min="0" max="100" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar Calificación</button>
        </form>
    </div>
    </body>
    </html>
    PHP;

    file_put_contents("$carpeta/Calificar.php", $contenido);
}
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
    $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $titulo);
    $nombre_archivo = $nombre_carpeta . "/Curso_" . str_replace(' ', '_', $titulo) . ".php";

    $contenido = "
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    \$curso_id = $id_curso;
    \$titulo = '$titulo'; // Definir la variable título para evitar errores

    function listarArchivos(\$periodo) {
        global \$conexion, \$curso_id;
        \$result = \$conexion->query(\"SELECT * FROM tareas WHERE curso_id=\$curso_id AND periodo='\$periodo'\");
        echo '<div class=\"list-group\">';
        while (\$archivo = \$result->fetch_assoc()) {
            \$archivo_path = \$archivo['archivo'];
            echo '<div class=\"list-group-item\">';
            echo '<p><strong>' . basename(\$archivo_path) . '</strong> - ' . ucfirst(\$archivo['tipo']) . '</p>';
            echo '<a href=\"' . \$archivo_path . '\" class=\"btn btn-primary btn-sm\" download>Descargar</a>';
            if (\$archivo['tipo'] == 'tarea') {
                echo ' | <a href=\"../../../php/calificar.php?tarea_id=' . \$archivo['id'] . '\" class=\"btn btn-warning btn-sm\">Calificar</a>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST['subir_archivo'])) {
        \$periodo = \$_POST['periodo'];
        \$tipo = \$_POST['tipo'];
        \$texto = \$_POST['texto'] ?? ''; // Capturar el texto si se envía
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
        <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\">
    </head>
    <body>
    <div class=\"container mt-5\">
        <h1 class=\"text-center mb-4\">Curso: <?php echo \$titulo; ?></h1>

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

        <div class=\"accordion\" id=\"accordionExample\">
            <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"headingOne\">
                    <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseOne\" aria-expanded=\"true\" aria-controls=\"collapseOne\">
                        Primer Periodo
                    </button>
                </h2>
                <div id=\"collapseOne\" class=\"accordion-collapse collapse show\" aria-labelledby=\"headingOne\" data-bs-parent=\"#accordionExample\">
                    <div class=\"accordion-body\">
                        <?php listarArchivos('primer_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"headingOne\">
                    <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseOne\" aria-expanded=\"true\" aria-controls=\"collapseOne\">
                        Segundo Periodo
                    </button>
                </h2>
                <div id=\"collapseOne\" class=\"accordion-collapse collapse show\" aria-labelledby=\"headingOne\" data-bs-parent=\"#accordionExample\">
                    <div class=\"accordion-body\">
                        <?php listarArchivos('segundo_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"headingOne\">
                    <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseOne\" aria-expanded=\"true\" aria-controls=\"collapseOne\">
                        Tercer Periodo
                    </button>
                </h2>
                <div id=\"collapseOne\" class=\"accordion-collapse collapse show\" aria-labelledby=\"headingOne\" data-bs-parent=\"#accordionExample\">
                    <div class=\"accordion-body\">
                        <?php listarArchivos('tercer_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class=\"accordion-item\">
                <h2 class=\"accordion-header\" id=\"headingOne\">
                    <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseOne\" aria-expanded=\"true\" aria-controls=\"collapseOne\">
                        Cuarto Periodo
                    </button>
                </h2>
                <div id=\"collapseOne\" class=\"accordion-collapse collapse show\" aria-labelledby=\"headingOne\" data-bs-parent=\"#accordionExample\">
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

    file_put_contents($nombre_archivo, $contenido);
    return $nombre_archivo;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Gestión de Cursos</h1>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
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
        <button type="submit" name="agregar_curso" class="btn btn-primary w-100">Agregar Curso</button>
    </form>

    <div class="accordion" id="accordionCursos">
        <?php
        $result = $conexion->query("SELECT * FROM cursos");
        if ($result->num_rows > 0) {
            while ($curso = $result->fetch_assoc()) {
                $archivo_curso = 'Cursos/' . str_replace(' ', '_', $curso['titulo']) . '/Curso_' . str_replace(' ', '_', $curso['titulo']) . '.php';
                echo '<div class="accordion-item">';
                echo '<h2 class="accordion-header" id="heading' . $curso['id'] . '">';
                echo '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $curso['id'] . '" aria-expanded="false" aria-controls="collapse' . $curso['id'] . '">';
                echo $curso['titulo'];
                echo '</button>';
                echo '</h2>';
                echo '<div id="collapse' . $curso['id'] . '" class="accordion-collapse collapse" aria-labelledby="heading' . $curso['id'] . '" data-bs-parent="#accordionCursos">';
                echo '<div class="accordion-body">';
                echo '<img src="' . $curso['imagen'] . '" class="img-fluid mb-3" alt="Imagen del curso">';
                echo '<p>' . $curso['descripcion'] . '</p>';
                echo '<a href="' . $archivo_curso . '" class="btn btn-info mb-2">Ver Curso</a> ';
                echo '<a href="?eliminar=' . $curso['id'] . '" class="btn btn-danger mb-2">Eliminar Curso</a> ';
                echo '<a href="editar_curso.php?id=' . $curso['id'] . '" class="btn btn-warning mb-2">Editar Curso</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No hay cursos disponibles.</p>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

