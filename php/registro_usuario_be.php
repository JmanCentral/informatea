<?php
include_once 'conexion_be.php';
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$foto = $_POST['foto'];
$rol = $_POST['rol'];
$contrasena = $_POST['contrasena'];
$contrasena = hash('sha512',$contrasena);

$query = "INSERT INTO login ( rol, nombre, correo, foto, contrasena) 
    VALUES ('$rol','$nombre','$correo', '$foto','$contrasena')";
//Verificar que el correo no se repita en la base de datos
$verificar_correo = mysqli_query($conexion, "SELECT * FROM login WHERE correo='$correo'");
if (mysqli_num_rows($verificar_correo)>0){
    echo '
    <script>
        alert("Este correo ya esta registrado, intenta con otro diferente");
        window.location = "login.php";
    </script>
    ';
    exit();
}
$ejecutar = mysqli_query($conexion, $query);
if ($ejecutar){
    echo '
        <script>
            alert("Usuario almacenado correctamente");
            window.location = "login.php";
        </script>
    ';
}else{
    echo '
        <script>
            alert("Intentelo nuevamente");
            window.location = "login.php";
        </script>
        ';
}
mysqli_close($conexion);
?>
