<?php
include 'conexion_be.php';
session_start();

if (!isset($_GET['evaluacion_id'])) {
    echo '<p class="alert alert-danger">Evaluación no encontrada.</p>';
    exit;
}



$evaluacion_id = $_GET['evaluacion_id'];
$curso_id = isset($_GET['curso_id']) ? $_GET['curso_id'] : null;

// Obtener los datos de la evaluación
$evaluacion = $conexion->query("SELECT * FROM evaluaciones WHERE id = $evaluacion_id")->fetch_assoc();

// Obtener las preguntas de la evaluación
$preguntas = $conexion->query("SELECT * FROM preguntas_evaluaciones WHERE evaluacion_id = $evaluacion_id");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $evaluacion['titulo']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center"><?php echo $evaluacion['titulo']; ?></h1>
    <p class="text-center"><?php echo $evaluacion['descripcion']; ?></p>

    <form action="procesar_respuesta.php" method="POST">
        
        <input type="hidden" name="evaluacion_id" value="<?php echo $evaluacion_id; ?>">
        <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">

        <?php while ($pregunta = $preguntas->fetch_assoc()) { ?>
            <div class="mb-3">
                <p><strong><?php echo $pregunta['pregunta']; ?></strong></p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" value="a" required>
                    <label class="form-check-label"><?php echo $pregunta['opcion_a']; ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" value="b">
                    <label class="form-check-label"><?php echo $pregunta['opcion_b']; ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" value="c">
                    <label class="form-check-label"><?php echo $pregunta['opcion_c']; ?></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="respuestas[<?php echo $pregunta['id']; ?>]" value="d">
                    <label class="form-check-label"><?php echo $pregunta['opcion_d']; ?></label>
                </div>
            </div>
        <?php } ?>

        <button type="submit" class="btn btn-success w-100">Enviar Respuestas</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
