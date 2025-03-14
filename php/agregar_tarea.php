<?php
// Incluir la conexión a la base de datos
include 'conexion_be.php';

$curso_id = $_GET['curso_id'] ?? '';  

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
    // Obtener los datos del formulario
    $curso_id = $_POST['curso_id'];
    $periodo = $_POST['periodo'];
    $tipo = $_POST['tipo'];
    $texto = $_POST['texto'] ?? '';
    $archivo = $_FILES['archivo']['name'] ?? '';
    $ruta_temporal = $_FILES['archivo']['tmp_name'] ?? '';

    // Validar que se haya subido un archivo o se haya enviado texto
    if (!empty($archivo) || !empty($texto)) {
        // Si se subió un archivo, moverlo a la carpeta del curso
        if (!empty($archivo)) {
            $ruta_destino = 'Cursos/' . $curso_id . '/' . $archivo;
            if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                // Insertar la nueva tarea en la base de datos
                $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$ruta_destino', '$tipo')");
                echo '<p class="alert alert-success">Archivo subido correctamente.</p>';
            } else {
                echo '<p class="alert alert-danger">Error al subir el archivo.</p>';
            }
        } elseif (!empty($texto)) {
            // Insertar la nueva tarea en la base de datos
            $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$texto', 'texto')");
            echo '<p class="alert alert-success">Texto guardado correctamente.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Debes subir un archivo o escribir un texto.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Agregar Tarea</h1>
        <form method="POST" enctype="multipart/form-data">
            <!-- Campo oculto para el ID del curso -->
            <input type="hidden" name="curso_id" value="<?php echo $_GET['curso_id']; ?>">

            <!-- Selección del periodo -->
            <div class="mb-3">
                <label for="periodo" class="form-label">Periodo</label>
                <select name="periodo" id="periodo" class="form-select" required>
                    <option value="primer_periodo">Primer Periodo</option>
                    <option value="segundo_periodo">Segundo Periodo</option>
                    <option value="tercer_periodo">Tercer Periodo</option>
                    <option value="cuarto_periodo">Cuarto Periodo</option>
                </select>
            </div>

            <!-- Selección del tipo de tarea -->
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select" required>
                    <option value="material">Material</option>
                    <option value="tarea">Tarea</option>
                </select>
            </div>

            <!-- Campo de texto -->
            <div class="mb-3">
                <label for="texto" class="form-label">Escribir Texto (opcional)</label>
                <textarea name="texto" id="texto" rows="3" class="form-control"></textarea>
            </div>

            <!-- Subir archivo -->
            <div class="mb-3">
                <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
                <input type="file" name="archivo" id="archivo" class="form-control">
            </div>

            <!-- Botón de enviar -->
            <button type="submit" name="subir_archivo" class="btn btn-primary">Subir Tarea</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>