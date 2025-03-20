<?php
class Curso {
    private $conexion;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearCurso($titulo, $descripcion, $imagen) {
        // Crear la carpeta del curso
        $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $titulo);
        $ruta_imagen = $nombre_carpeta . '/' . $imagen;

        if (!is_dir($nombre_carpeta)) {
            mkdir($nombre_carpeta, 0777, true);
        }

        // Mover la imagen a la carpeta del curso
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen)) {
            // Insertar el curso en la base de datos
            $sql = "INSERT INTO cursos (titulo, descripcion, imagen) VALUES (?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sss", $titulo, $descripcion, $ruta_imagen);

            if ($stmt->execute()) {
                $id_curso = $stmt->insert_id;
                $archivo_curso = $this->crearEstructuraCurso($titulo, $id_curso);
                return [
                    'success' => true,
                    'message' => "Curso creado: <a href='$archivo_curso'>$archivo_curso</a>"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Error al guardar los datos del curso en la base de datos."
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "Error al subir la imagen del curso."
            ];
        }
    }

    // Método para eliminar un curso
    public function eliminarCurso($id) {
        // Obtener el título del curso para eliminar su carpeta y archivo PHP
        $result = $this->conexion->query("SELECT titulo FROM cursos WHERE id = $id");
        if ($curso = $result->fetch_assoc()) {
            $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $curso['titulo']);
            $archivo_php = $nombre_carpeta . "/Curso_" . str_replace(' ', '_', $curso['titulo']) . ".php";

            // Verificar y eliminar el archivo PHP del curso si existe
            if (file_exists($archivo_php) && !unlink($archivo_php)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo eliminar el archivo PHP del curso.'
                ];
            }

            // Eliminar la carpeta del curso y su contenido
            if ($this->eliminarCarpeta($nombre_carpeta)) {
                // Eliminar el curso de la base de datos
                if ($this->conexion->query("DELETE FROM cursos WHERE id = $id") === TRUE) {
                    return [
                        'success' => true,
                        'message' => 'Curso eliminado correctamente.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Error al eliminar el curso de la base de datos.'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo eliminar la carpeta del curso.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Curso no encontrado.'
            ];
        }
    }

    // Método para eliminar una carpeta y su contenido
    private function eliminarCarpeta($carpeta) {
        if (is_dir($carpeta)) {
            $archivos = array_diff(scandir($carpeta), ['.', '..']);
            foreach ($archivos as $archivo) {
                $ruta = $carpeta . DIRECTORY_SEPARATOR . $archivo;
                is_dir($ruta) ? $this->eliminarCarpeta($ruta) : unlink($ruta);
            }
            return rmdir($carpeta);
        }
        return false;
    }

    // Método para crear la estructura del curso
    public function crearEstructuraCurso($titulo, $id_curso) {
        // Crear la carpeta del curso
        $nombre_carpeta = $this->crearCarpetaCurso($titulo);

        // Generar el archivo PHP del curso
        $nombre_archivo = $nombre_carpeta . "/Curso_" . str_replace(' ', '_', $titulo) . ".php";
        $contenido = $this->generarContenidoArchivo($titulo, $id_curso);

        // Guardar el archivo
        file_put_contents($nombre_archivo, $contenido);

        return $nombre_archivo;
    }

    // Método para crear la carpeta del curso
    private function crearCarpetaCurso($titulo) {
        $nombre_carpeta = 'Cursos/' . str_replace(' ', '_', $titulo);
        if (!is_dir($nombre_carpeta)) {
            mkdir($nombre_carpeta, 0777, true);
        }
        return $nombre_carpeta;
    }

    // Método para generar el contenido del archivo PHP
    private function generarContenidoArchivo($titulo, $id_curso) {
        $contenido = "<?php\n";
        $contenido .= "include __DIR__ . '/../../../php/conexion_be.php';\n";
        $contenido .= "\$curso_id = $id_curso;\n";
        $contenido .= "\$titulo = '$titulo';\n\n";

        // Función para listar respuestas de estudiantes
        $contenido .= $this->generarFuncionListarRespuestas();

        // Script para manejar la calificación con AJAX
        $contenido .= $this->generarScriptAJAX();

        $contenido .= "?>\n";

        return $contenido;
    }

    // Método para generar la función listarRespuestas
    private function generarFuncionListarRespuestas() {
        return "
        function listarRespuestas(\$periodo) {
            global \$conexion, \$curso_id;

            // Obtener las tareas del periodo
            \$tareas = \$conexion->query(\"SELECT id FROM tareas WHERE curso_id = \$curso_id AND periodo = '\$periodo'\");

            if (\$tareas->num_rows > 0) {
                echo '<table class=\"table table-bordered\">';
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

                while (\$tarea = \$tareas->fetch_assoc()) {
                    // Obtener las respuestas de los estudiantes para esta tarea
                    \$respuestas = \$conexion->query(\"SELECT * FROM respuestas_tareas WHERE tarea_id = \" . \$tarea['id']);

                    if (\$respuestas->num_rows > 0) {
                        while (\$respuesta = \$respuestas->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . \$respuesta['estudiante_correo'] . '</td>';
                            echo '<td>';
                            if (!empty(\$respuesta['archivo'])) {
                                \$ruta_archivo = 'Respuestas/' . basename(\$respuesta['archivo']);
                                echo '<a href=\"' . \$ruta_archivo . '\" download>' . basename(\$respuesta['archivo']) . '</a>';
                            } else {
                                echo 'N/A';
                            }
                            echo '</td>';
                            echo '<td>' . (\$respuesta['texto'] ?? 'N/A') . '</td>';

                            // Verificar si la respuesta ya tiene una calificación
                            \$calificacion_query = \$conexion->query(\"SELECT * FROM calificaciones WHERE respuesta_id = \" . \$respuesta['id']);
                            \$calificacion = \$calificacion_query->fetch_assoc();

                            if (\$calificacion) {
                                echo '<td>' . \$calificacion['calificacion'] . '</td>';
                                echo '<td><button class=\"btn btn-secondary btn-sm\" disabled><i class=\"bi bi-pencil-square\"></i> Calificado</button></td>';
                            } else {
                                echo '<td>Sin calificar</td>';
                                echo '<td>';
                                echo '<button class=\"btn btn-info btn-sm\" data-bs-toggle=\"modal\" data-bs-target=\"#calificarModal' . \$respuesta['id'] . '\">
                                        <i class=\"bi bi-pencil-square\"></i> Calificar
                                      </button>';
                            }

                            echo '
                            <div class=\"modal fade\" id=\"calificarModal' . \$respuesta['id'] . '\" tabindex=\"-1\" aria-labelledby=\"calificarModalLabel' . \$respuesta['id'] . '\" aria-hidden=\"true\">
                                <div class=\"modal-dialog\">
                                    <div class=\"modal-content\">
                                        <div class=\"modal-header\">
                                            <h5 class=\"modal-title\" id=\"calificarModalLabel' . \$respuesta['id'] . '\">Calificar Respuesta</h5>
                                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
                                        </div>
                                        <div class=\"modal-body\">
                                            <form id=\"formCalificar' . \$respuesta['id'] . '\" method=\"POST\" action=\"../../../php/guardar_calificacion.php\">
                                                <input type=\"hidden\" name=\"respuesta_id\" value=\"' . \$respuesta['id'] . '\">
                                                <div class=\"mb-3\">
                                                    <label for=\"calificacion\" class=\"form-label\">Calificación</label>
                                                    <input type=\"number\" name=\"calificacion\" id=\"calificacion\" class=\"form-control\" min=\"0\" max=\"10\" step=\"0.1\" required>
                                                </div>
                                                <div class=\"mb-3\">
                                                    <label for=\"observaciones\" class=\"form-label\">Observaciones</label>
                                                    <textarea name=\"observaciones\" id=\"observaciones\" class=\"form-control\" rows=\"3\"></textarea>
                                                </div>
                                                <button type=\"submit\" name=\"guardar_calificacion\" class=\"btn btn-primary\">Guardar Calificación</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<tr><td colspan=\"5\">No hay respuestas subidas por los estudiantes.</td></tr>';
                    }
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p class=\"text-muted\">No hay tareas en este periodo.</p>';
            }
        }\n";
    }

    // Método para generar el script AJAX
    private function generarScriptAJAX() {
        return "
        echo '<script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>
        <script>
            $(document).ready(function() {
                $(\"form[id^='formCalificar']\").on(\"submit\", function(event) {
                    event.preventDefault(); // Evitar el envío tradicional del formulario

                    var form = $(this);
                    var url = form.attr(\"action\");
                    var formData = form.serialize(); // Serializar los datos del formulario

                    // Enviar la solicitud AJAX
                    $.ajax({
                        url: url,
                        type: \"POST\",
                        data: formData,
                        dataType: \"json\",
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload(); // Recargar la página
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function() {
                            alert(\"Error al enviar la solicitud.\");
                        }
                    });
                });
            });
        </script>';\n";
    }




}
?>