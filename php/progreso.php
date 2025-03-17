<?php

include 'conexion_be.php'; // Conexión a la base de datos
include 'ProgresoEstudiante.php'; // Incluir la clase ProgresoEstudiante


$curso_id = $_GET['curso_id'] ?? null;

// Crear una instancia de la clase ProgresoEstudiante
$progresoEstudiante = new ProgresoEstudiante($conexion);

// Obtener el progreso de todos los estudiantes en el curso
$progreso_estudiantes = $progresoEstudiante->obtenerProgresoEstudiantes($curso_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso de Estudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress {
            height: 20px;
            margin-bottom: 10px;
        }
        .progress-bar {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Progreso de Estudiantes</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Tareas Completadas</th>
                    <th>Total de Tareas</th>
                    <th>Progreso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progreso_estudiantes as $progreso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($progreso['estudiante_correo']); ?></td>
                        <td><?php echo $progreso['tareas_completadas']; ?></td>
                        <td><?php echo $progreso['total_tareas']; ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: <?php echo $progreso['progreso']; ?>%;" 
                                     aria-valuenow="<?php echo $progreso['progreso']; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?php echo round($progreso['progreso'], 2); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>