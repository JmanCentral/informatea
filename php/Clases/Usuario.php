<?php
class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function registrarUsuario($nombre, $correo, $foto, $rol, $contrasena) {
        $contrasena = hash('sha512', $contrasena);

        // Verificar si el correo ya existe
        $stmt = $this->conexion->prepare("SELECT * FROM login WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return [
                'success' => false,
                'message' => 'Este correo ya está registrado, intenta con otro diferente'
            ];
        }

        // Insertar usuario
        $stmt = $this->conexion->prepare("INSERT INTO login (rol, nombre, correo, foto, contrasena) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $rol, $nombre, $correo, $foto, $contrasena);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Usuario almacenado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al registrar el usuario'
            ];
        }
    }

    public function loginUsuario($correo, $contrasena) {
        session_start(); // Iniciar sesión

        $contrasena = hash('sha512', $contrasena);

        // Verificar si el usuario existe en la base de datos
        $stmt = $this->conexion->prepare("SELECT * FROM login WHERE correo = ? AND contrasena = ?");
        $stmt->bind_param("ss", $correo, $contrasena);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $rol = $row['rol'];

            $_SESSION['correo'] = $correo;

            // Si el rol es 2, guardar el correo en una variable de sesión especial
            if ($rol == 2) {
                $_SESSION['correo_estudiante'] = $correo;
                return [
                    'success' => true,
                    'redirect' => 'CursosEstudiantes.php'
                ];
            }

            // Redirigir según el rol
            return [
                'success' => true,
                'redirect' => $rol == 1 ? 'Profesor.php' : 'Administrador.php'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos. Por favor, verifique los datos ingresados.'
            ];
        }
    }
}
?>