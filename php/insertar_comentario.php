<?php
session_start();
include 'conexion_be.php'; // Conexión a la base de datos

// Verificar que el estudiante haya iniciado sesión
if (!isset($_SESSION['correo'])) {
    die(json_encode(["error" => "No has iniciado sesión."]));
}

$estudiante_correo = $_SESSION['correo']; // Correo del estudiante

// Leer el cuerpo de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);

// Validar los datos recibidos
if (!isset($data['curso_id']) || !isset($data['comentario'])) {
    die(json_encode(["error" => "Faltan datos requeridos."]));
}

$curso_id = $data['curso_id'];
$comentario = $data['comentario'];

// Insertar el comentario en la base de datos
$sql = "INSERT INTO comentarios (curso_id, estudiante_correo, comentario, fecha) 
        VALUES ('$curso_id', '$estudiante_correo', '$comentario', NOW())";

if ($conexion->query($sql) === TRUE) {
    echo json_encode(["success" => "Comentario agregado con éxito."]);
} else {
    echo json_encode(["error" => "Error al agregar el comentario: " . $conexion->error]);
}

// Cerrar conexión
$conexion->close();
?>
