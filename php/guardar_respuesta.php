<?php
include 'conexion_be.php'; // Conexión a la base de datos

session_start(); // Iniciar sesión

// Verificar si el estudiante ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    echo '<p class="alert alert-danger">Debes iniciar sesión para enviar una respuesta.</p>';
    exit;
}


// Obtener el correo del estudiante desde la sesión
$estudiante_correo = $_SESSION['correo'];

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_respuesta'])) {
    
    $id = $_POST['id'];
    $tarea_id = $_POST['tarea_id'];
    $archivo = $_FILES['archivo']['name'] ?? '';
    $texto = $_POST['texto'] ?? '';
    $ruta_temporal = $_FILES['archivo']['tmp_name'] ?? '';

    // Validar que se haya subido un archivo o se haya enviado texto
    if (!empty($archivo) || !empty($texto)) {
        if (!empty($archivo)) {
            // Crear la carpeta de respuestas si no existe
            $carpeta_respuestas = 'Respuestas/' . $tarea_id;
            if (!file_exists($carpeta_respuestas)) {
                mkdir($carpeta_respuestas, 0777, true);
            }

            // Mover el archivo subido a la carpeta de respuestas
            $ruta_destino = $carpeta_respuestas . '/' . $archivo;
            if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                // Insertar la respuesta en la base de datos
                $conexion->query("INSERT INTO respuestas_tareas (tarea_id, estudiante_correo, archivo, texto) VALUES ($tarea_id, '$estudiante_correo', '$ruta_destino', '$texto')");
                echo '<p class="alert alert-success">Respuesta enviada correctamente.</p>';
            } else {
                echo '<p class="alert alert-danger">Error al subir el archivo.</p>';
            }
        } else {
            // Insertar la respuesta en la base de datos (solo texto)
            $conexion->query("INSERT INTO respuestas_tareas (id , tarea_id, estudiante_id, texto) VALUES ($tarea_id, '$estudiante_id', '$texto')");
            echo '<p class="alert alert-success">Respuesta enviada correctamente.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Debes subir un archivo o escribir un texto.</p>';
    }
} else {
    echo '<p class="alert alert-danger">Acceso no autorizado.</p>';
}
?>

<!-- Modal para responder a la tarea -->
<div class="modal fade" id="responderModal<?php echo $tarea['id']; ?>" tabindex="-1" aria-labelledby="responderModalLabel<?php echo $tarea['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responderModalLabel<?php echo $tarea['id']; ?>">Responder a la Tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="guardar_respuesta.php" enctype="multipart/form-data">
                    <input type="hidden" name="tarea_id" value="<?php echo $tarea['id']; ?>">
                    <input type="hidden" name="estudiante_id" value="<?php echo $correo_estudiante; ?>">

                    <div class="mb-3">
                        <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
                        <input type="file" name="archivo" id="archivo" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="texto" class="form-label">Escribir Texto (opcional)</label>
                        <textarea name="texto" id="texto" rows="3" class="form-control"></textarea>
                    </div>

                    <button type="submit" name="subir_respuesta" class="btn btn-primary">Enviar Respuesta</button>
                </form>
            </div>
        </div>
    </div>
</div>