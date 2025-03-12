<?php
include 'conexion_be.php';

$id = $_GET['id'];

$query = "DELETE FROM login WHERE id='$id'";
if (mysqli_query($conexion, $query)) {
    echo "Usuario eliminado exitosamente.";
} else {
    echo "Error: " . mysqli_error($conexion);
}

header("Location: Administrador.php");
exit();
?>
