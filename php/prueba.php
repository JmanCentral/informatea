<?php
include __DIR__ . '/../../../php/conexion_be.php';
$curso_id = 41;
$titulo = 'Curso primero'; // Definir la variable título para evitar errores

// Función para listar los archivos subidos para cada periodo
function listarArchivos($periodo) {
    global $conexion, $curso_id;

    // Consulta para obtener los archivos del curso y periodo actual
    $result = $conexion->query("SELECT * FROM tareas WHERE curso_id = $curso_id AND periodo = '$periodo'");

    if (!$result) {
        echo "<p class='alert alert-danger'>Error en la consulta: " . $conexion->error . "</p>";
        return;
    }

    echo '<div class="list-group">';
    if ($result->num_rows > 0) {
        while ($archivo = $result->fetch_assoc()) {
            $archivo_path = $archivo['archivo'];
            $tipo = ucfirst($archivo['tipo']);

            echo '<div class="list-group-item">';
            echo "<p><strong>" . basename($archivo_path) . "</strong> - $tipo</p>";
            echo "<a href='$archivo_path' class='btn btn-primary btn-sm' download>Descargar</a>";

            // Si es una tarea, mostrar la opción de calificar
            if ($archivo['tipo'] == 'tarea') {
                echo " | <a href='../../../php/calificar.php?tarea_id=" . $archivo['id'] . "' class='btn btn-warning btn-sm'>Calificar</a>";
            }
            echo '</div>';
        }
    } else {
        echo '<p>No hay archivos disponibles para este periodo.</p>';
    }
    echo '</div>';
}

// Procesar la subida de archivos por el profesor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
    $periodo = $_POST['periodo'];
    $tipo = $_POST['tipo'];
    $texto = $_POST['texto'] ?? ''; // Capturar el texto si se envía
    $archivo = $_FILES['archivo']['name'] ?? '';
    $ruta = 'Cursos/' . str_replace(' ', '_', $titulo) . '/' . uniqid() . '_' . $archivo;

    // Subida del archivo y registro en la base de datos
    if (!empty($archivo) && move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
        $ruta_relativa = $ruta;
        $sql = "INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$ruta_relativa', '$tipo')";

        if ($conexion->query($sql) === TRUE) {
            echo '<p class="alert alert-success">Archivo subido correctamente.</p>';
        } else {
            echo '<p class="alert alert-danger">Error al guardar el archivo: ' . $conexion->error . '</p>';
        }
    } elseif (!empty($texto)) {
        $sql = "INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$texto', 'texto')";
        if ($conexion->query($sql) === TRUE) {
            echo '<p class="alert alert-success">Texto guardado correctamente.</p>';
        } else {
            echo '<p class="alert alert-danger">Error al guardar el texto: ' . $conexion->error . '</p>';
        }
    } else {
        echo '<p class="alert alert-danger">No se ha subido ningún archivo o texto.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Curso: <?php echo $titulo; ?></h1>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="periodo" class="form-label">Periodo</label>
                <select name="periodo" id="periodo" class="form-select">
                    <option value="primer_periodo">Primer Periodo</option>
                    <option value="segundo_periodo">Segundo Periodo</option>
                    <option value="tercer_periodo">Tercer Periodo</option>
                    <option value="cuarto_periodo">Cuarto Periodo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="tipo" class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="material">Material</option>
                    <option value="tarea">Tarea</option>
                </select>
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label for="texto" class="form-label">Escribir Texto (opcional)</label>
            <textarea name="texto" id="texto" rows="3" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
            <input type="file" name="archivo" id="archivo" class="form-control">
        </div>

        <button type="submit" name="subir_archivo" class="btn btn-primary w-100">Subir Archivo o Enviar Texto</button>
    </form>

    <div class="accordion" id="accordionExample">
        <?php
        $periodos = ['primer_periodo', 'segundo_periodo', 'tercer_periodo', 'cuarto_periodo'];
        foreach ($periodos as $periodo) {
            echo '
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading' . $periodo . '">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $periodo . '" aria-expanded="true" aria-controls="collapse' . $periodo . '">
                        ' . ucfirst(str_replace('_', ' ', $periodo)) . '
                    </button>
                </h2>
                <div id="collapse' . $periodo . '" class="accordion-collapse collapse show" aria-labelledby="heading' . $periodo . '" data-bs-parent="#accordionExample">
                    <div class="accordion-body">';
            listarArchivos($periodo);
            echo '  </div>
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
