<?php
include __DIR__ . '/conexion_be.php';

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
    <style>
        .card {
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 20px;
        }
    </style>
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

        <!-- Botón para abrir el modal de agregar pregunta -->
        <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#modalAgregarPregunta">
            Agregar Pregunta
        </button>

        <!-- Botón para guardar la evaluación -->
        <button type="submit" class="btn btn-primary w-100">Guardar Evaluación</button>
    </form>
</div>

<!-- Modal para agregar preguntas -->
<div class="modal fade" id="modalAgregarPregunta" tabindex="-1" aria-labelledby="modalAgregarPreguntaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarPreguntaLabel">Agregar Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="preguntaTexto" class="form-label">Pregunta</label>
                    <input type="text" id="preguntaTexto" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="opcionA" class="form-label">Opción A</label>
                    <input type="text" id="opcionA" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="opcionB" class="form-label">Opción B</label>
                    <input type="text" id="opcionB" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="opcionC" class="form-label">Opción C</label>
                    <input type="text" id="opcionC" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="opcionD" class="form-label">Opción D</label>
                    <input type="text" id="opcionD" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="respuestaCorrecta" class="form-label">Respuesta Correcta</label>
                    <select id="respuestaCorrecta" class="form-select" required>
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                        <option value="d">D</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="agregarPregunta()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let contadorPreguntas = 0;

    function agregarPregunta() {
        const preguntaTexto = document.getElementById('preguntaTexto').value;
        const opcionA = document.getElementById('opcionA').value;
        const opcionB = document.getElementById('opcionB').value;
        const opcionC = document.getElementById('opcionC').value;
        const opcionD = document.getElementById('opcionD').value;
        const respuestaCorrecta = document.getElementById('respuestaCorrecta').value;

        if (!preguntaTexto || !opcionA || !opcionB || !opcionC || !opcionD || !respuestaCorrecta) {
            alert('Por favor, completa todos los campos.');
            return;
        }

        contadorPreguntas++;
        const preguntasDiv = document.getElementById('preguntas');

        const nuevaPregunta = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pregunta ${contadorPreguntas}</h5>
                    <input type="hidden" name="preguntas[${contadorPreguntas}][texto]" value="${preguntaTexto}">
                    <input type="hidden" name="preguntas[${contadorPreguntas}][opcion_a]" value="${opcionA}">
                    <input type="hidden" name="preguntas[${contadorPreguntas}][opcion_b]" value="${opcionB}">
                    <input type="hidden" name="preguntas[${contadorPreguntas}][opcion_c]" value="${opcionC}">
                    <input type="hidden" name="preguntas[${contadorPreguntas}][opcion_d]" value="${opcionD}">
                    <input type="hidden" name="preguntas[${contadorPreguntas}][respuesta_correcta]" value="${respuestaCorrecta}">
                    <p><strong>Pregunta:</strong> ${preguntaTexto}</p>
                    <p><strong>Opciones:</strong></p>
                    <ul>
                        <li>A) ${opcionA}</li>
                        <li>B) ${opcionB}</li>
                        <li>C) ${opcionC}</li>
                        <li>D) ${opcionD}</li>
                    </ul>
                    <p><strong>Respuesta Correcta:</strong> ${respuestaCorrecta.toUpperCase()}</p>
                </div>
            </div>
        `;

        preguntasDiv.insertAdjacentHTML('beforeend', nuevaPregunta);

        // Limpiar el modal
        document.getElementById('preguntaTexto').value = '';
        document.getElementById('opcionA').value = '';
        document.getElementById('opcionB').value = '';
        document.getElementById('opcionC').value = '';
        document.getElementById('opcionD').value = '';
        document.getElementById('respuestaCorrecta').value = 'a';

        // Cerrar el modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarPregunta'));
        modal.hide();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>