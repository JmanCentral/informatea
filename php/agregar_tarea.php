<?php
include 'conexion_be.php';

$curso_id = $_GET['curso_id'] ?? '';

// Crear una nueva tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
    $curso_id = $_POST['curso_id'];
    $periodo = $_POST['periodo'];
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'] ?? '';
    $archivo = $_FILES['archivo']['name'] ?? '';
    $ruta_temporal = $_FILES['archivo']['tmp_name'] ?? '';

    if (!empty($archivo) || !empty($descripcion)) {
        if (!empty($archivo)) {
            // Crear la carpeta del curso si no existe
            $carpeta_curso = 'Cursos/' . $curso_id;
            if (!file_exists($carpeta_curso)) {
                mkdir($carpeta_curso, 0777, true);
            }

            $ruta_destino = $carpeta_curso . '/' . $archivo;
            if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
                // Insertar la nueva tarea en la base de datos
                $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo, descripcion) VALUES ($curso_id, '$periodo', '$ruta_destino', '$tipo', '$descripcion')");
                echo '<p class="alert alert-success">Archivo subido correctamente.</p>';
            } else {
                echo '<p class="alert alert-danger">Error al subir el archivo.</p>';
            }
        } elseif (!empty($descripcion)) {
            // Insertar la nueva tarea en la base de datos
            $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo, descripcion) VALUES ($curso_id, '$periodo', NULL, '$tipo', '$descripcion')");
            echo '<p class="alert alert-success">Tarea guardada correctamente.</p>';
        }
    } else {
        echo '<p class="alert alert-danger">Debes subir un archivo o escribir una descripción.</p>';
    }
}

// Eliminar una tarea
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conexion->query("DELETE FROM tareas WHERE id = $id");
    echo '<p class="alert alert-success">Tarea eliminada correctamente.</p>';
}

// Actualizar una tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_tarea'])) {
    $id = $_POST['id'];
    $periodo = $_POST['periodo'];
    $tipo = $_POST['tipo'];
    $descripcion = $_POST['descripcion'] ?? '';
    $archivo = $_FILES['archivo']['name'] ?? '';
    $ruta_temporal = $_FILES['archivo']['tmp_name'] ?? '';

    if (!empty($archivo)) {
        // Crear la carpeta del curso si no existe
        $carpeta_curso = 'Cursos/' . $curso_id;
        if (!file_exists($carpeta_curso)) {
            mkdir($carpeta_curso, 0777, true);
        }

        $ruta_destino = $carpeta_curso . '/' . $archivo;
        if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
            $conexion->query("UPDATE tareas SET periodo = '$periodo', archivo = '$ruta_destino', tipo = '$tipo', descripcion = '$descripcion' WHERE id = $id");
            echo '<p class="alert alert-success">Tarea actualizada correctamente.</p>';
        } else {
            echo '<p class="alert alert-danger">Error al subir el archivo.</p>';
        }
    } else {
        $conexion->query("UPDATE tareas SET periodo = '$periodo', tipo = '$tipo', descripcion = '$descripcion' WHERE id = $id");
        echo '<p class="alert alert-success">Tarea actualizada correctamente.</p>';
    }
}

// Obtener todas las tareas del curso
$tareas = $conexion->query("SELECT * FROM tareas WHERE curso_id = $curso_id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas</title>
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
        .container {
            max-width: 800px;
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
        .btn-primary {
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
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
        <h4 class="ms-3">Gestión de Tareas</h4>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="content">
        <div class="container">
            <h1>Gestión de Tareas</h1>

            <!-- Botón para agregar tarea -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarTareaModal">
                Agregar Tarea
            </button>

            <!-- Tabla de tareas -->
            <?php if ($tareas->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Periodo</th>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tarea = $tareas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $tarea['id']; ?></td>
                                <td><?php echo $tarea['periodo']; ?></td>
                                <td><?php echo $tarea['tipo']; ?></td>
                                <td><?php echo $tarea['archivo']; ?></td>
                                <td><?php echo $tarea['descripcion']; ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarTareaModal<?php echo $tarea['id']; ?>">
                                        Editar
                                    </button>
                                    <a href="?curso_id=<?php echo $curso_id; ?>&eliminar=<?php echo $tarea['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta tarea?')">Eliminar</a>
                                </td>
                            </tr>

                            <!-- Modal para editar tarea -->
                            <div class="modal fade" id="editarTareaModal<?php echo $tarea['id']; ?>" tabindex="-1" aria-labelledby="editarTareaModalLabel<?php echo $tarea['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarTareaModalLabel<?php echo $tarea['id']; ?>">Editar Tarea</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">

                                                <div class="mb-3">
                                                    <label for="periodo" class="form-label">Periodo</label>
                                                    <select name="periodo" id="periodo" class="form-select" required>
                                                        <option value="primer_periodo" <?php echo ($tarea['periodo'] == 'primer_periodo') ? 'selected' : ''; ?>>Primer Periodo</option>
                                                        <option value="segundo_periodo" <?php echo ($tarea['periodo'] == 'segundo_periodo') ? 'selected' : ''; ?>>Segundo Periodo</option>
                                                        <option value="tercer_periodo" <?php echo ($tarea['periodo'] == 'tercer_periodo') ? 'selected' : ''; ?>>Tercer Periodo</option>
                                                        <option value="cuarto_periodo" <?php echo ($tarea['periodo'] == 'cuarto_periodo') ? 'selected' : ''; ?>>Cuarto Periodo</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="tipo" class="form-label">Tipo</label>
                                                    <select name="tipo" id="tipo" class="form-select" required>
                                                        <option value="material" <?php echo ($tarea['tipo'] == 'material') ? 'selected' : ''; ?>>Material</option>
                                                        <option value="tarea" <?php echo ($tarea['tipo'] == 'tarea') ? 'selected' : ''; ?>>Tarea</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="descripcion" class="form-label">Descripción</label>
                                                    <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required><?php echo $tarea['descripcion']; ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
                                                    <input type="file" name="archivo" id="archivo" class="form-control">
                                                </div>

                                                <button type="submit" name="editar_tarea" class="btn btn-primary">Guardar Cambios</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="alert alert-info">No hay tareas registradas.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para agregar tarea -->
    <div class="modal fade" id="agregarTareaModal" tabindex="-1" aria-labelledby="agregarTareaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agregarTareaModalLabel">Agregar Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">

                        <div class="mb-3">
                            <label for="periodo" class="form-label">Periodo</label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="primer_periodo">Primer Periodo</option>
                                <option value="segundo_periodo">Segundo Periodo</option>
                                <option value="tercer_periodo">Tercer Periodo</option>
                                <option value="cuarto_periodo">Cuarto Periodo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="material">Material</option>
                                <option value="tarea">Tarea</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3" class="form-control" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="archivo" class="form-label">Subir Archivo (opcional)</label>
                            <input type="file" name="archivo" id="archivo" class="form-control">
                        </div>

                        <button type="submit" name="subir_archivo" class="btn btn-primary">Subir Tarea</button>
                    </form>
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