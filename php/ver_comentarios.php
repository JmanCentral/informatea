<?php
include 'conexion_be.php'; 

$query = "SELECT * FROM comentarios ORDER BY fecha DESC";
$result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comentarios del Curso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Comentarios Recientes</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Curso ID</th>
                        <th>Estudiante</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comentario = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $comentario['id']; ?></td>
                            <td><?php echo $comentario['curso_id']; ?></td>
                            <td><?php echo $comentario['estudiante_correo']; ?></td>
                            <td><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></td>
                            <td><?php echo $comentario['fecha']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No hay comentarios disponibles.</div>
        <?php endif; ?>
    </div>
</body>
</html>