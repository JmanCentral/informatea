<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesion</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

<main>

    <div class="contenedor__todo">
        <div class="caja__trasera">
            <div class="caja__trasera-login">
                <h3>¿Ya tienes una cuenta?</h3>
                <p>Inicia sesión para entrar en la página</p>
                <button id="btn__iniciar-sesion">Iniciar Sesión</button>
            </div>
            <div class="caja__trasera-register">
                <h3>¿Aún no tienes una cuenta?</h3>
                <p>Regístrate para que puedas iniciar sesión</p>
                <button id="btn__registrarse">Regístrarse</button>
            </div>
        </div>

        <!--Formulario de Login y registro-->
        <div class="contenedor__login-register">
            <!--Login-->
            <form action="login_usuario_be.php" method="POST" class="formulario__login">
                <h2>Iniciar Sesión</h2>
                <input type="text" placeholder="Correo Electronico" name="correo">
                <input type="password" placeholder="Contraseña" name="contrasena">
                <button>Entrar</button>
            </form>

            <!--Register-->
            <form action="registro_usuario_be.php" method="POST" class="formulario__register">
                <h2>Registrarse</h2>

                <input type="text" placeholder="Nombre completo" name="nombre">
                <input type="text" placeholder="Correo Electrónico" name="correo">
                <input type="file"  name="foto">
                <label for="rol">Selecciona tu rol</label>
                <select name="rol" id="rol">
                    <option value="2">Estudiante</option>
                    <option value="1">Profesor</option>
                    <option value="3">Administrador</option>
                </select>
                <label for="foto">Carga una foto de perfil</label>
                <input type="password" placeholder="Contraseña" name="contrasena">
                <button type="submit">Registrarse</button>


            </form>
        </div>
    </div>

</main>

<script src="../js/login.js"></script>
</body>
</html>