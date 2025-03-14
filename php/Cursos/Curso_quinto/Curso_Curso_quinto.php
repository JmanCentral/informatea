
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    $curso_id = 78;
    $titulo = 'Curso quinto';

    // Función para listar archivos y agregar la opción de eliminar
    function listarArchivos($periodo) {
        global $conexion, $curso_id;
        $result = $conexion->query("SELECT * FROM tareas WHERE curso_id=$curso_id AND periodo='$periodo'");
        echo '<div class="list-group">';
        while ($archivo = $result->fetch_assoc()) {
            $archivo_path = $archivo['archivo'];
            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<p><strong>' . basename($archivo_path) . '</strong> - ' . ucfirst($archivo['tipo']) . '</p>';
            echo '</div>';
            echo '<div>';
            echo '<a href="' . $archivo_path . '" class="btn btn-primary btn-sm me-2" download>Descargar</a>';
            echo '<a href="../../../php/TareasSubidas.php?periodo=' . $archivo['periodo'] . '&tarea_id=' . $archivo['id'] . '" class="btn btn-warning btn-sm me-2">Ver Tareas Subidas</a>';
            echo '<a href="../../../php/EliminarTarea.php?id=' . $archivo['id'] . '&archivo=' . urlencode($archivo['archivo']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar esta tarea?\');">Eliminar</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
        $periodo = $_POST['periodo'];
        $tipo = $_POST['tipo'];
        $texto = $_POST['texto'] ?? '';
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8f9fa;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .btn-custom {
                margin: 5px;
            }
            .accordion-button {
                background-color: #007bff;
                color: white;
                font-weight: bold;
            }
            .accordion-button:not(.collapsed) {
                background-color: #0056b3;
                color: white;
            }
            .accordion-body {
                background-color: #ffffff;
                border: 1px solid #dee2e6;
                border-top: none;
            }
            .list-group-item {
                border: 1px solid #dee2e6;
                margin-bottom: 10px;
                border-radius: 5px;
            }
            .list-group-item:hover {
                background-color: #f1f1f1;
            }
        </style>
    </head>
    <body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Curso: <?php echo $titulo; ?></h1>

        <!-- Botones de acción -->
        <div class="text-center mb-4">
            <a href="../../../php/crear_evaluacion.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-success btn-custom">Crear Evaluación</a>
            <a href="../../../php/ver_respuestas.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-success btn-custom">Consultar Evaluación</a>
            <a href="../../../php/ver_comentarios.php?curso_id=<?php echo $curso_id; ?>" class="btn btn-info btn-custom">Ver Comentarios</a>
        </div>

        <!-- Formulario para subir archivos o enviar texto -->
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

        <!-- Acordeón para los períodos -->
        <div class="accordion" id="accordionExample">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                        Primer Periodo
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <?php listarArchivos('primer_periodo'); ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                        Segundo Periodo
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <?php listarArchivos('segundo_periodo'); ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                        Tercer Periodo
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <?php listarArchivos('tercer_periodo'); ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                        Cuarto Periodo
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse show">
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
    