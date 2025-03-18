<?php
include 'conexion_be.php'; // Conexión a la base de datos

session_start(); // Iniciar sesión

// Verificar si el profesor ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para calificar.']);
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respuesta_id'])) {
    $respuesta_id = $_POST['respuesta_id'];
    $calificacion = $_POST['calificacion'];
    $observaciones = $_POST['observaciones'];
    $fecha_calificacion = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    // Validar que la calificación esté en el rango correcto
    if ($calificacion >= 0 && $calificacion <= 10) {
        // Insertar la calificación en la base de datos
        $query = "INSERT INTO calificaciones (calificacion, observaciones, fecha_calificacion, respuesta_id) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("dssi", $calificacion, $observaciones, $fecha_calificacion, $respuesta_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Calificación guardada correctamente.', 'calificacion' => $calificacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar la calificación.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'La calificación debe estar entre 0 y 10.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
}
?>