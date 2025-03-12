<?php
include 'conexion_be.php'; // Conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conexion->query("SELECT * FROM cursos WHERE id = $id");
    $curso = $result->fetch_assoc();
}

$mensaje = ""; // Variable para mostrar mensaje de confirmación

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $id = $_POST['id'];

    if (!empty($_FILES['imagen']['name'])) {
        $imagen = $_FILES['imagen']['name'];
        $ruta_imagen = 'Cursos/' . str_replace(' ', '_', $titulo) . '/' . $imagen;

        // Mover la nueva imagen a la carpeta correspondiente
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen);

        // Actualizar los datos del curso junto con la imagen
        $sql = "UPDATE cursos SET titulo = '$titulo', descripcion = '$descripcion', imagen = '$ruta_imagen' WHERE id = $id";
    } else {
        // Actualizar solo título y descripción si no se subió una nueva imagen
        $sql = "UPDATE cursos SET titulo = '$titulo', descripcion = '$descripcion' WHERE id = $id";
    }

    if ($conexion->query($sql) === TRUE) {
        $mensaje = '<p class="alert alert-success">Curso actualizado correctamente.</p>';
    } else {
        $mensaje = '<p class="alert alert-danger">Error al actualizar el curso.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Editar Curso</h1>

    <!-- Mostrar mensaje de confirmación -->
    <?php echo $mensaje; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">

        <div class="mb-3">
            <label for="titulo" class="form-label">Título del Curso</label>
            <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo $curso['titulo']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required><?php echo $curso['descripcion']; ?></textarea>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen del Curso (opcional)</label>
            <input type="file" name="imagen" id="imagen" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
    </form>

    <a href="Profesor.php" class="btn btn-secondary w-100 mt-3">Volver</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
