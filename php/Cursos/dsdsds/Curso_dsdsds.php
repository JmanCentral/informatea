
    <?php
    include __DIR__ . '/../../../php/conexion_be.php';
    $curso_id = 95;
    $titulo = 'dsdsds';

    // Función para listar respuestas de estudiantes por periodo
    function listarRespuestas($periodo) {
        global $conexion, $curso_id;

        // Obtener las tareas del periodo
        $tareas = $conexion->query("SELECT id FROM tareas WHERE curso_id = $curso_id AND periodo = '$periodo'");

        if ($tareas->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Estudiante</th>';
            echo '<th>Archivo</th>';
            echo '<th>Texto</th>';
            echo '<th>Calificación</th>';
            echo '<th>Acciones</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($tarea = $tareas->fetch_assoc()) {
                // Obtener las respuestas de los estudiantes para esta tarea
                $respuestas = $conexion->query("SELECT * FROM respuestas_tareas WHERE tarea_id = " . $tarea['id']);

                if ($respuestas->num_rows > 0) {
                    while ($respuesta = $respuestas->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $respuesta['estudiante_correo'] . '</td>';
                        echo '<td>';
                        if (!empty($respuesta['archivo'])) {
                            echo '<a href="' . $respuesta['archivo'] . '" download>' . basename($respuesta['archivo']) . '</a>';
                        } else {
                            echo 'N/A';
                        }
                        echo '</td>';
                        echo '<td>' . ($respuesta['texto'] ?? 'N/A') . '</td>';
                        echo '<td>' . ($respuesta['calificacion'] ?? 'Sin calificar') . '</td>';
                        echo '<td>';
                        echo '<button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#calificarModal' . $respuesta['id'] . '">
                                <i class="bi bi-pencil-square"></i> Calificar
                              </button>';

                        // Modal para calificar la respuesta
                        echo '
                        <div class="modal fade" id="calificarModal' . $respuesta['id'] . '" tabindex="-1" aria-labelledby="calificarModalLabel' . $respuesta['id'] . '" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="calificarModalLabel' . $respuesta['id'] . '">Calificar Respuesta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="guardar_calificacion.php">
                                            <input type="hidden" name="respuesta_id" value="' . $respuesta['id'] . '">
                                            <div class="mb-3">
                                                <label for="calificacion" class="form-label">Calificación</label>
                                                <input type="number" name="calificacion" id="calificacion" class="form-control" min="0" max="10" step="0.1" required>
                                            </div>
                                            <button type="submit" name="guardar_calificacion" class="btn btn-primary">Guardar Calificación</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No hay respuestas subidas por los estudiantes.</td></tr>';
                }
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p class="text-muted">No hay tareas en este periodo.</p>';
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
            <a href="../../../php/agregar_tarea.php?curso_id=<?php echo $curso_id; ?>">Ver Tareas</a>
            <a href="#" onclick="mostrarPeriodos()">Ver Periodos</a>
            <a href="../../../php/profesor.php">Volver a Cursos</a> <!-- Nuevo enlace para volver a los cursos generales -->
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
                            <?php listarRespuestas('primer_periodo'); ?>
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
                            <?php listarRespuestas('segundo_periodo'); ?>
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
                            <?php listarRespuestas('tercer_periodo'); ?>
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
                            <?php listarRespuestas('cuarto_periodo'); ?>
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
    