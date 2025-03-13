<?php
include 'conexion_be.php'; // Conexión a la base de datos

session_start(); // Iniciar sesión

// Obtener los datos necesarios de la URL
$tarea_id = $_GET['tarea_id'] ?? '';  
$curso_id = $_GET['curso_id'] ?? '';  
$correo_estudiante = $_SESSION['correo'] ?? 'correo_no_definido';

// Validar que los IDs lleguen correctamente
if (empty($tarea_id) || empty($curso_id)) {
    die('<p class="alert alert-danger">ID de la tarea o curso no proporcionado.</p>');
}

// Obtener el título del curso desde la base de datos
$query_curso = "SELECT titulo FROM cursos WHERE id = $curso_id";
$result_curso = $conexion->query($query_curso);

if ($result_curso->num_rows > 0) {
    $curso = $result_curso->fetch_assoc();

    // Reemplazar espacios por guiones bajos en el nombre del curso
    $nombre_curso = str_replace(' ', '_', $curso['titulo']);
    $nombre_carpeta = 'Cursos/' . $nombre_curso;

    // Verificar si la carpeta existe, si no, crearla
    if (!is_dir($nombre_carpeta)) {
        mkdir($nombre_carpeta, 0777, true); // Crear carpeta con permisos completos
    }

    // Obtener el nombre del estudiante desde la tabla `login` usando el correo
    $query_estudiante = "SELECT nombre FROM login WHERE correo = '$correo_estudiante'";
    $result_estudiante = $conexion->query($query_estudiante);

    if ($result_estudiante->num_rows > 0) {
        $estudiante = $result_estudiante->fetch_assoc()['nombre'];

        // Procesar la subida del archivo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $archivo = $_FILES['archivo']['name'];
            $ruta = $nombre_carpeta . '/' . $archivo; // Guardar en la carpeta del curso

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
                // Registrar la tarea en la base de datos (inserción inicial)
                $sql = "INSERT INTO tareas (curso_id, archivo, tipo, correo_estudiante, tarea_padre_id, estudiante) 
                        VALUES ($curso_id, '$ruta', 'tarea_estudiante', '$correo_estudiante', $tarea_id, '$estudiante')";

                if ($conexion->query($sql) === TRUE) {
                    // **Obtener el ID recién insertado**
                    $nuevo_tarea_id = $conexion->insert_id;

                    // **Actualizar el campo `tarea_id` en el mismo registro**
                    $update_sql = "UPDATE tareas SET tarea_id = $nuevo_tarea_id WHERE id = $nuevo_tarea_id";

                    if ($conexion->query($update_sql) === TRUE) {
                        echo '<p class="alert alert-success mt-3">Tarea subida y registrada correctamente.</p>';
                    } else {
                        echo '<p class="alert alert-danger mt-3">Error al actualizar el campo tarea_id.</p>';
                        echo '<p>Error SQL: ' . $conexion->error . '</p>';
                    }
                } else {
                    echo '<p class="alert alert-danger mt-3">Error al registrar la tarea.</p>';
                    echo '<p>Error SQL: ' . $conexion->error . '</p>';
                }
            } else {
                echo '<p class="alert alert-danger mt-3">Error al subir el archivo.</p>';
            }
        }
    } else {
        echo '<p class="alert alert-danger">Estudiante no encontrado.</p>';
    }
} else {
    echo '<p class="alert alert-danger">Curso no encontrado.</p>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Subir Tarea</h1>
    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label for="archivo" class="form-label">Seleccionar Archivo</label>
            <input type="file" name="archivo" id="archivo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Subir Tarea</button>
    </form>
</div>
</body>
</html>
