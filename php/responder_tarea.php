<?php
include 'conexion_be.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_tarea'])) {
    $tarea_id = $_POST['tarea_id'];
    $curso_id = $_POST['curso_id'];
    $archivo_respuesta = $_FILES['archivo_respuesta']['name'] ?? '';
    $texto_respuesta = $_POST['texto_respuesta'] ?? '';
    $ruta_temporal = $_FILES['archivo_respuesta']['tmp_name'] ?? '';

    if (!empty($archivo_respuesta) || !empty($texto_respuesta)) {
        if (!empty($archivo_respuesta)) {
            // Crear la carpeta de respuestas si no existe
            $carpeta_respuestas = 'Respuestas/' . $curso_id;
            if (!file_exists($carpeta_respuestas)) {
                mkdir($carpeta_respuestas, 0777, true);
            }

            $ruta_destino = $carpeta_respuestas . '/' . $archivo_respuesta;
            if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                // Insertar la respuesta en la base de datos
                $conexion->query("INSERT INTO respuestas_tareas (tarea_id, estudiante_id, archivo, texto) VALUES ($tarea_id, 1, '$ruta_destino', '$texto_respuesta')");
                echo '<p class="alert alert-success">Respuesta enviada correctamente.</p>';
            } else {
                echo '<p class="alert alert-danger">Error al subir el archivo.</p>';
            }
        } elseif (!empty($texto_respuesta)) {
            // Insertar la respuesta en la base de datos
            $conexion->query("INSERT INTO respuestas_tareas (tarea_id, estudiante_id, texto) VALUES ($tarea_id, 1, '$texto_respuesta')");
            echo '<p class="alert alert-success">Respuesta enviada correctamente.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Debes subir un archivo o escribir un texto.</p>';
    }
}
?>