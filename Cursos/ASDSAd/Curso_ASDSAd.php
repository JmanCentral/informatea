
    <?php
    // Asegurar que la conexión se incluya correctamente usando __DIR__
    $conexion_ruta = dirname(__DIR__, 3) . '/php/conexion_be.php';
    if (file_exists($conexion_ruta)) {
        include $conexion_ruta;
    } else {
        die('Error: No se pudo encontrar el archivo de conexión.');
    }

    $curso_id = 18;

    function listarArchivos($periodo) {
        global $conexion, $curso_id;
        if (!$conexion) {
            die('Error de conexión a la base de datos.');
        }
        $result = $conexion->query("SELECT * FROM tareas WHERE curso_id=$curso_id AND periodo='$periodo'");
        while ($archivo = $result->fetch_assoc()) {
            echo '<p>' . basename($archivo['archivo']) . ' - ' . $archivo['tipo'] . '</p>';
            echo '<a href="' . $archivo['archivo'] . '" download>Descargar</a>';
            if ($archivo['tipo'] == 'tarea') {
                echo ' | <a href="../../../php/calificar.php?tarea_id=' . $archivo['id'] . '">Calificar</a>';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_archivo'])) {
        $periodo = $_POST['periodo'];
        $tipo = $_POST['tipo'];
        $archivo = $_FILES['archivo']['name'];
        $ruta = '../Cursos/ASDSAd/' . $archivo;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
            $conexion->query("INSERT INTO tareas (curso_id, periodo, archivo, tipo) 
                              VALUES ($curso_id, '$periodo', '$ruta', '$tipo')");
            echo '<p>Archivo subido correctamente.</p>';
        } else {
            echo '<p>Error al subir el archivo.</p>';
        }
    }
    ?>

    <h1><?php echo 'Curso: ASDSAd'; ?></h1>

    <form method='POST' enctype='multipart/form-data'>
        <select name='periodo'>
            <option value='primer_periodo'>Primer Periodo</option>
            <option value='segundo_periodo'>Segundo Periodo</option>
            <option value='tercer_periodo'>Tercer Periodo</option>
            <option value='cuarto_periodo'>Cuarto Periodo</option>
        </select>
        <input type='file' name='archivo' required>
        <select name='tipo'>
            <option value='material'>Material</option>
            <option value='tarea'>Tarea</option>
        </select>
        <button type='submit' name='subir_archivo'>Subir Archivo</button>
    </form>

    <h2>Primer Periodo</h2>
    <?php listarArchivos('primer_periodo'); ?>

    <h2>Segundo Periodo</h2>
    <?php listarArchivos('segundo_periodo'); ?>

    <h2>Tercer Periodo</h2>
    <?php listarArchivos('tercer_periodo'); ?>

    <h2>Cuarto Periodo</h2>
    <?php listarArchivos('cuarto_periodo'); ?>
    