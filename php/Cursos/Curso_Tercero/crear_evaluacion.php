<?php

include __DIR__ . '/../../conexion_be.php';


$curso_id = $_GET['curso_id']; // ID del curso para el cual se crea la evaluación

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar el formulario para crear la evaluación
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];

    // Insertar la evaluación en la base de datos
    $conexion->query("INSERT INTO evaluaciones (curso_id, titulo, descripcion) VALUES ($curso_id, '$titulo', '$descripcion')");
    $evaluacion_id = $conexion->insert_id; // Obtener el ID de la evaluación recién creada

    // Insertar las preguntas y respuestas
    foreach ($_POST['preguntas'] as $pregunta) {
        $pregunta_texto = $pregunta['texto'];
        $opcion_a = $pregunta['opcion_a'];
        $opcion_b = $pregunta['opcion_b'];
        $opcion_c = $pregunta['opcion_c'];
        $opcion_d = $pregunta['opcion_d'];
        $respuesta_correcta = $pregunta['respuesta_correcta'];

        $conexion->query("INSERT INTO preguntas_evaluaciones (evaluacion_id, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta) VALUES ($evaluacion_id, '$pregunta_texto', '$opcion_a', '$opcion_b', '$opcion_c', '$opcion_d', '$respuesta_correcta')");
    }

    echo '<p class="alert alert-success">Evaluación creada correctamente.</p>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Evaluación</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Crear Evaluación</h1>

    <form method="POST">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título de la Evaluación</label>
            <input type="text" name="titulo" id="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" rows="3" class="form-control"></textarea>
        </div>

        <h3>Preguntas</h3>
        <div id="preguntas">
            <!-- Aquí se agregarán dinámicamente las preguntas -->
        </div>

        <button type="button" class="btn btn-secondary mb-3" onclick="agregarPregunta()">Agregar Pregunta</button>
        <button type="submit" class="btn btn-primary w-100">Guardar Evaluación</button>
    </form>
</div>

<script>
    let contadorPreguntas = 0;

    function agregarPregunta() {
        contadorPreguntas++;
        const preguntasDiv = document.getElementById('preguntas');

        const nuevaPregunta = `
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pregunta ${contadorPreguntas}</h5>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_texto" class="form-label">Pregunta</label>
                        <input type="text" name="preguntas[${contadorPreguntas}][texto]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_opcion_a" class="form-label">Opción A</label>
                        <input type="text" name="preguntas[${contadorPreguntas}][opcion_a]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_opcion_b" class="form-label">Opción B</label>
                        <input type="text" name="preguntas[${contadorPreguntas}][opcion_b]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_opcion_c" class="form-label">Opción C</label>
                        <input type="text" name="preguntas[${contadorPreguntas}][opcion_c]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_opcion_d" class="form-label">Opción D</label>
                        <input type="text" name="preguntas[${contadorPreguntas}][opcion_d]" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pregunta_${contadorPreguntas}_respuesta_correcta" class="form-label">Respuesta Correcta</label>
                        <select name="preguntas[${contadorPreguntas}][respuesta_correcta]" class="form-select" required>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                </div>
            </div>
        `;

        preguntasDiv.insertAdjacentHTML('beforeend', nuevaPregunta);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>