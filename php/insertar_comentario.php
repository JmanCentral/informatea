<?php


include 'conexion_be.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $curso_id = $_POST['curso_id'];
    $comentario = $_POST['comentario'];
    $estudiante_correo = $_SESSION['correo']; // Asume que el correo del estudiante está en la sesión

    // Insertar el comentario en la base de datos
    $sql = "INSERT INTO comentarios (curso_id, estudiante_correo, comentario, fecha) VALUES (?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iss", $curso_id, $estudiante_correo, $comentario);

    if ($stmt->execute()) {
        header("Location: CursosEstudiantes.php"); // Redirigir de vuelta a la página de cursos
        exit();
    } else {
        echo "Error al insertar el comentario: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}
?>