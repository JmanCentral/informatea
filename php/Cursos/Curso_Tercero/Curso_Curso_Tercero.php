
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    $curso_id = 55;
    $titulo = 'Curso Tercero'; // Definir la variable título para evitar errores

    function listarArchivos($periodo) {
        global $conexion, $curso_id;
        $result = $conexion->query("SELECT * FROM tareas WHERE curso_id=$curso_id AND periodo='$periodo'");
        echo '<div class="list-group">';
        while ($archivo = $result->fetch_assoc()) {
            $archivo_path = $archivo['archivo'];
            echo '<div class="list-group-item">';
            echo '<p><strong>' . basename($archivo_path) . '</strong> - ' . ucfirst($archivo['tipo']) . '</p>';
            echo '<a href="' . $archivo_path . '" class="btn btn-primary btn-sm" download>Descargar</a>';
            if ($archivo['tipo'] == 'tarea') {
                echo ' | <a href="../../../php/calificar.php?tarea_id=' . $archivo['id'] . '" class="btn btn-warning btn-sm">Calificar</a>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
        $periodo = $_POST['periodo'];
        $tipo = $_POST['tipo'];
        $texto = $_POST['texto'] ?? ''; // Capturar el texto si se envía
        $archivo = $_FILES['archivo']['name'] ?? '';
        $ruta = __DIR__ . '/' . $archivo;

        if (!empty($archivo) && move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
            $ruta_relativa = 'Cursos/' . basename(__DIR__) . '/' . $archivo;
            $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$ruta_relativa', '$tipo')");
            echo '<p class="alert alert-success">Archivo subido correctamente.</p>';
        } elseif (!empty($texto)) {
            $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo) VALUES ($curso_id, '$periodo', '$texto', 'texto')");
            echo '<p class="alert alert-success">Texto guardado correctamente.</p>';
        } else {
            echo '<p class="alert alert-danger">Error al subir el archivo o enviar el texto.</p>';
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

        <a href="../php/crear_evaluacion.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-success mb-4">Crear Evaluación</a>

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
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Primer Periodo
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <?php listarArchivos('primer_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Segundo Periodo
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <?php listarArchivos('segundo_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Tercer Periodo
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <?php listarArchivos('tercer_periodo'); ?>
                    </div>
                </div>
            </div>
            <!-- Otros periodos -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Cuarto Periodo
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <?php listarArchivos('cuarto_periodo'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    