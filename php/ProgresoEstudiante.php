<?php
class ProgresoEstudiante {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerProgresoEstudiantes($curso_id) {
        // Consulta para obtener el total de tareas de tipo 'tarea' en el curso
        $query_total_tareas = "
            SELECT COUNT(*) AS total_tareas 
            FROM tareas 
            WHERE curso_id = ? AND tipo = 'tarea'  -- Solo contar tareas de tipo 'tarea'
        ";
        $stmt_total = $this->conexion->prepare($query_total_tareas);
        $stmt_total->bind_param("i", $curso_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $row_total = $result_total->fetch_assoc();
        $total_tareas = $row_total['total_tareas'];

        // Consulta para obtener las tareas completadas por cada estudiante (solo de tipo 'tarea')
        $query_tareas_completadas = "
            SELECT 
                rt.estudiante_correo,
                COUNT(rt.tarea_id) AS tareas_completadas
            FROM 
                respuestas_tareas rt
            JOIN 
                tareas t ON rt.tarea_id = t.id
            WHERE 
                t.curso_id = ? AND t.tipo = 'tarea'  -- Solo contar tareas de tipo 'tarea'
            GROUP BY 
                rt.estudiante_correo
        ";
        $stmt_completadas = $this->conexion->prepare($query_tareas_completadas);
        $stmt_completadas->bind_param("i", $curso_id);
        $stmt_completadas->execute();
        $result_completadas = $stmt_completadas->get_result();

        $progreso_estudiantes = [];
        while ($row = $result_completadas->fetch_assoc()) {
            $progreso = ($row['tareas_completadas'] / $total_tareas) * 100;
            $progreso_estudiantes[] = [
                'estudiante_correo' => $row['estudiante_correo'],
                'tareas_completadas' => $row['tareas_completadas'],
                'total_tareas' => $total_tareas,
                'progreso' => $progreso
            ];
        }

        return $progreso_estudiantes;
    }

    public function obtenerProgresoEstudiante($curso_id, $estudiante_correo) {
        // Consulta para obtener el total de tareas de tipo 'tarea' en el curso
        $query_total_tareas = "
            SELECT COUNT(*) AS total_tareas 
            FROM tareas 
            WHERE curso_id = ? AND tipo = 'tarea'  -- Solo contar tareas de tipo 'tarea'
        ";
        $stmt_total = $this->conexion->prepare($query_total_tareas);
        $stmt_total->bind_param("i", $curso_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $row_total = $result_total->fetch_assoc();
        $total_tareas = $row_total['total_tareas'];

        // Consulta para obtener las tareas completadas por el estudiante específico (solo de tipo 'tarea')
        $query_tareas_completadas = "
            SELECT 
                COUNT(rt.tarea_id) AS tareas_completadas
            FROM 
                respuestas_tareas rt
            JOIN 
                tareas t ON rt.tarea_id = t.id
            WHERE 
                t.curso_id = ? AND t.tipo = 'tarea' AND rt.estudiante_correo = ?
        ";
        $stmt_completadas = $this->conexion->prepare($query_tareas_completadas);
        $stmt_completadas->bind_param("is", $curso_id, $estudiante_correo);
        $stmt_completadas->execute();
        $result_completadas = $stmt_completadas->get_result();
        $row_completadas = $result_completadas->fetch_assoc();
        $tareas_completadas = $row_completadas['tareas_completadas'];

        // Calcular el progreso
        $progreso = ($tareas_completadas / $total_tareas) * 100;

        return [
            'estudiante_correo' => $estudiante_correo,
            'tareas_completadas' => $tareas_completadas,
            'total_tareas' => $total_tareas,
            'progreso' => $progreso
        ];
    }
}
?>