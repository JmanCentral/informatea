<?php
include __DIR__ . '/conexion_be.php';

// Verifica si 'curso_id' está presente en la URL y es un número válido
if (!isset($_GET['curso_id']) || !is_numeric($_GET['curso_id'])) {
    die("ID de curso no válido.");
}

$curso_id = $_GET['curso_id'];

// Obtener las evaluaciones del curso
$query_evaluaciones = $conexion->prepare("
    SELECT id, titulo, descripcion, fecha_creacion 
    FROM evaluaciones 
    WHERE curso_id = ?
");
$query_evaluaciones->bind_param("i", $curso_id);
$query_evaluaciones->execute();
$result_evaluaciones = $query_evaluaciones->get_result();
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones del Curso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        <a href="crear_evaluacion.php?curso_id=<?php echo $curso_id; ?>">Crear Evaluación</a>
        <a href="ver_respuestas.php?curso_id=<?php echo $curso_id; ?>">Consultar Evaluación</a>
        <a href="ver_comentarios.php?curso_id=<?php echo $curso_id; ?>">Ver Comentarios</a>
        <a href="agregar_tarea.php?curso_id=<?php echo $curso_id; ?>">Ver Tareas</a>
        <a href="#" onclick="mostrarPeriodos()">Ver Periodos</a>
        <a href="profesor.php">Volver a Cursos</a>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>

    <!-- Barra superior -->
    <div class="navbar">
        <button onclick="toggleSidebar()">☰</button>
        <h4 class="ms-3">Evaluaciones del Curso</h4>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="content">
        <div class="container mt-5">
            <h2 class="mb-4">Evaluaciones del Curso</h2>

            <?php if ($result_evaluaciones->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($evaluacion = $result_evaluaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($evaluacion['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($evaluacion['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($evaluacion['fecha_creacion']); ?></td>
                                <td>
                                    <a href="ver_respuestas.php?evaluacion_id=<?php echo $evaluacion['id']; ?>" class="btn btn-info btn-sm">
                                        Ver Respuestas
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning">No hay evaluaciones disponibles para este curso.</div>
            <?php endif; ?>
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