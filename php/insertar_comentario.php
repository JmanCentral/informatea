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
if (empty($data['curso_id']) || empty($data['comentario'])) {
    die(json_encode(["error" => "Faltan datos requeridos o están vacíos."]));
}

$curso_id = intval($data['curso_id']); // Convertir a entero
$comentario = trim($data['comentario']); // Limpiar el comentario

// Validar que el curso_id sea un número válido
if ($curso_id <= 0) {
    die(json_encode(["error" => "El ID del curso no es válido."]));
}

// Validar que el comentario no esté vacío
if (empty($comentario)) {
    die(json_encode(["error" => "El comentario no puede estar vacío."]));
}

// Insertar el comentario en la base de datos usando consultas preparadas
$sql = "INSERT INTO comentarios (curso_id, estudiante_correo, comentario, fecha) 
        VALUES (?, ?, ?, NOW())";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("iss", $curso_id, $estudiante_correo, $comentario);
    if ($stmt->execute()) {
        echo json_encode(["success" => "Comentario agregado con éxito."]);
    } else {
        echo json_encode(["error" => "Error al agregar el comentario: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["error" => "Error en la preparación de la consulta: " . $conexion->error]);
}

// Cerrar conexión
$conexion->close();
?>