
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    $curso_id = 82;
    $titulo = 'curso noveno';

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
                margin: 0;
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
            }
            .sidebar {
                width: 250px;
                height: 100vh;
                background-color: #343a40;
                color: white;
                position: fixed;
                top: 0;
                left: -250px;
                overflow-y: auto;
                padding-top: 20px;
                transition: left 0.3s;
            }
            .sidebar.active {
                left: 0;
            }
            .sidebar a {
                color: white;
                padding: 10px 15px;
                text-decoration: none;
                display: block;
            }
            .sidebar a:hover {
                background-color: #495057;
            }
            .content {
                margin-left: 0;
                padding: 20px;
                transition: margin-left 0.3s;
                margin-top: 60px;
            }
            .content.active {
                margin-left: 250px;
            }
            .navbar {
                background-color: #007bff;
                color: white;
                padding: 10px 20px;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                display: flex;
                align-items: center;
                height: 60px;
            }
            .navbar button {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
            }
            .card-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }
            .card {
                flex: 1 1 calc(50% - 20px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .card img {
                max-width: 100%;
                height: 200px;
                object-fit: cover;
                border-radius: 10px 10px 0 0;
            }
            .logout {
                margin-left: auto;
                cursor: pointer;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <!-- Menú lateral -->
        <div class="sidebar" id="sidebar">
            <h4 class="text-center mb-4">Menú</h4>
            <a href="../../../php/crear_evaluacion.php?curso_id=<?php echo $curso_id; ?>">Crear Evaluación</a>
            <a href="../../../php/ver_respuestas.php?curso_id=<?php echo $curso_id; ?>">Consultar Evaluación</a>
            <a href="../../../php/ver_comentarios.php?curso_id=<?php echo $curso_id; ?>">Ver Comentarios</a>
            <a href="../../../php/agregar_tarea.php">Agregar Tarea</a>
            <a href="#" onclick="mostrarPeriodos()">Ver Periodos</a>
            <a href="../../../php/logout.php" class="logout">Cerrar Sesión</a>
        </div>

        <!-- Barra superior -->
        <div class="navbar">
            <button onclick="toggleSidebar()">☰</button>
            <h4 class="ms-3">Curso: <?php echo $titulo; ?></h4>
        </div>

        <!-- Contenido principal -->
        <div class="content" id="content">
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

        <script>
            // Función para mostrar/ocultar el menú lateral
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            }

            // Función para mostrar los periodos
            function mostrarPeriodos() {
                alert('Mostrando periodos...');
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    