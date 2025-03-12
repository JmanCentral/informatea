<?php
// Incluir el archivo de conexión
include 'conexion_be.php';

// Verificar si se recibió una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $imagen = $_POST['imagen'];

    // Actualizar la información del curso en la base de datos
    $sql = "UPDATE cursos SET titulo='$titulo', descripcion='$descripcion', imagen='$imagen' WHERE id='$id'";

    if ($conexion->query($sql) === TRUE) {
        echo "Curso actualizado con éxito.";
    } else {
        echo "Error al actualizar el curso: " . $conexion->error;
    }
}

// Cerrar conexión
$conexion->close();
?>
