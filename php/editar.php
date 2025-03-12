<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
</head>
<body>

<h1>Editar Usuario</h1>

<?php
include 'conexion_be.php';

$id = $_GET['id'];
$query = "SELECT * FROM login WHERE id='$id'";
$result = mysqli_query($conexion, $query);
$row = mysqli_fetch_assoc($result);
?>

<form action="editar.php?id=<?php echo $id; ?>" method="POST">
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" value="<?php echo $row['nombre']; ?>" required><br><br>

    <label for="correo">Correo:</label>
    <input type="email" name="correo" value="<?php echo $row['correo']; ?>" required><br><br>

    <label for="rol">Rol:</label>
    <select name="rol" required>
        <option value="1" <?php if ($row['rol'] == 1) echo 'selected'; ?>>Profesor</option>
        <option value="2" <?php if ($row['rol'] == 2) echo 'selected'; ?>>Estudiante</option>
        <option value="3" <?php if ($row['rol'] == 3) echo 'selected'; ?>>Administrador</option>
    </select><br><br>

    <input type="submit" value="Actualizar Usuario">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    $query = "UPDATE login SET nombre='$nombre', correo='$correo', rol='$rol' WHERE id='$id'";
    if (mysqli_query($conexion, $query)) {
        echo "Usuario actualizado exitosamente.";
        header("Location: Administrador.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>

<a href="Administrador.php">Volver a la lista de usuarios</a>

</body>
</html>
