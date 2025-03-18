<?php
include 'conexion_be.php'; // Conexión a la base de datos
include 'ProgresoEstudiante.php'; // Incluir la clase ProgresoEstudiante

// Obtener el curso_id y el correo del estudiante desde la URL
$curso_id = $_GET['curso_id'] ?? null;
$correo_estudiante = $_GET['correo'] ?? null;

// Validar que se hayan proporcionado los parámetros necesarios
if (!$curso_id || !$correo_estudiante) {
    die("Error: Se requieren el ID del curso y el correo del estudiante.");
}

// Crear una instancia de la clase ProgresoEstudiante
$progresoEstudiante = new ProgresoEstudiante($conexion);

// Obtener el progreso del estudiante específico en el curso
$progreso_estudiante = $progresoEstudiante->obtenerProgresoEstudiante($curso_id, $correo_estudiante);

// Verificar si se obtuvieron datos
if (!$progreso_estudiante) {
    die("No se encontró progreso para el estudiante especificado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progreso del Estudiante</title>
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
        <h2>Progreso del Estudiante</h2>
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
                <tr>
                    <td><?php echo htmlspecialchars($progreso_estudiante['estudiante_correo']); ?></td>
                    <td><?php echo $progreso_estudiante['tareas_completadas']; ?></td>
                    <td><?php echo $progreso_estudiante['total_tareas']; ?></td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: <?php echo $progreso_estudiante['progreso']; ?>%;" 
                                 aria-valuenow="<?php echo $progreso_estudiante['progreso']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo round($progreso_estudiante['progreso'], 2); ?>%
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>