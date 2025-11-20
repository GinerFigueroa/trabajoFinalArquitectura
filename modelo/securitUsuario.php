<?php
// Asegúrate de que 'Conexion.php' es el archivo con el patrón Singleton.
include_once('Conexion.php'); 

/**
 * Clase UsuarioDAO (Data Access Object)
 * Responsable única del acceso a la base de datos para la entidad 'usuarios'.
 * Utiliza el patrón Singleton para la conexión.
 */
class UsuarioDAO
{
    private $connection;

    // El constructor obtiene la ÚNICA instancia de la conexión Singleton
    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // Nota: Se eliminan todas las llamadas a conectarBD() y desConectarBD() 
    // porque el Singleton maneja la conexión abierta y lista para usar.

    /**
     * Valida si el login de usuario existe.
     * @return bool
     */
    public function validarLogin($login)
    {
        $sql = "SELECT usuario_usuario FROM usuarios WHERE usuario_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $numFilas = $resultado->num_rows;
        $stmt->close();

        return $numFilas == 1;
    }

    /**
     * Valida la clave de usuario usando hash.
     * @return bool
     */
    public function validarPassword($login, $password)
    {
        $sql = "SELECT usuario_clave FROM usuarios WHERE usuario_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $esValido = false;
        if ($fila = $resultado->fetch_assoc()) {
            $hashGuardado = $fila['usuario_clave'];
            if (password_verify($password, $hashGuardado)) {
                $esValido = true;
            }
        }
        
        $stmt->close();
        return $esValido;
    }

    /**
     * Valida si el usuario está activo.
     * @return bool
     */
    public function validarEstado($login)
    {
        $sql = "SELECT usuario_usuario FROM usuarios WHERE usuario_usuario = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $numFilas = $resultado->num_rows;
        
        $stmt->close();
        return $numFilas == 1;
    }

    /**
     * Obtiene todos los usuarios con la información de su rol.
     * @return array
     */
    public function obtenerTodosUsuarios()
    {
        // Se consolida la versión más completa de obtenerTodosUsuarios
        $sql = "SELECT u.id_usuario, u.usuario_usuario, u.nombre, u.apellido_paterno, 
                       u.apellido_materno, u.email, u.telefono, u.id_rol, u.activo,
                       r.nombre as rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario DESC"; // Se añade un orden para consistencia

        $resultado = $this->connection->query($sql);
        $usuarios = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila;
            }
        }
        return $usuarios;
    }

    /**
     * Obtiene los datos de un usuario específico por su login.
     * @return array|null
     */
    public function obtenerDatosUsuario($login)
    {
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.usuario_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        
        $stmt->close();
        return $usuario;
    }

    /**
     * Obtiene los datos de un usuario por su ID.
     * @return array|null
     */
    public function obtenerUsuarioPorId($idUsuario)
    {
        // Se consolida la versión simple que busca por ID
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        
        $stmt->close();
        return $usuario;
    }

    /**
     * Valida si un valor ya existe en un campo (ej: email, login).
     * @return bool
     */
    public function validarCampoUnico($campo, $valor)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Valida si un valor ya existe en un campo, excluyendo un usuario específico (para editar).
     * @return bool
     */
    public function validarCampoUnicoExcepto($campo, $valor, $idUsuario)
    {
        // Se consolida la versión de edición
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ? AND id_usuario != ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $valor, $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    // ====================================================================
    // MÉTODOS NUEVOS PARA RECUPERACIÓN DE CONTRASEÑA
    // ====================================================================

    /**
     * Valida si un email existe en el sistema y está activo
     * @return bool
     */
    public function validarEmailExiste($email)
    {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        return $fila['count'] > 0;
    }

    /**
     * Obtiene usuario por email
     * @return array|null
     */
    public function obtenerUsuarioPorEmail($email)
    {
        $sql = "SELECT id_usuario, email, creado_en FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    /**
     * Obtiene información completa del usuario por ID (incluyendo fecha de creación)
     * @return array|null
     */
    public function obtenerUsuarioCompletoPorId($idUsuario)
    {
        $sql = "SELECT id_usuario, email, creado_en FROM usuarios WHERE id_usuario = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    /**
     * Actualiza la contraseña de un usuario
     * @return bool
     */
    public function actualizarPassword($idUsuario, $nuevaPasswordHash)
    {
        $sql = "UPDATE usuarios SET usuario_clave = ? WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $nuevaPasswordHash, $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene información completa del usuario para privilegios
     * @return array|null
     */
    public function obtenerInformacionCompletaUsuario($login)
    {
        $sql = "SELECT id_usuario, id_rol, nombre, apellido_paterno, apellido_materno, email 
                FROM usuarios 
                WHERE usuario_usuario = ? AND activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }

    /**
     * Valida si un usuario está activo por email
     * @return bool
     */
    public function validarUsuarioActivoPorEmail($email)
    {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE email = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        return $fila['count'] > 0;
    }

    /**
     * Registra un nuevo usuario en el sistema
     * @return bool
     */
    public function registrarUsuario($datosUsuario)
    {
        $sql = "INSERT INTO usuarios (id_rol, email, usuario_clave, usuario_usuario, 
                                     nombre, apellido_paterno, apellido_materno, telefono, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("isssssss", 
            $datosUsuario['id_rol'],
            $datosUsuario['email'],
            $datosUsuario['usuario_clave'],
            $datosUsuario['usuario_usuario'],
            $datosUsuario['nombre'],
            $datosUsuario['apellido_paterno'],
            $datosUsuario['apellido_materno'],
            $datosUsuario['telefono']
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Actualiza los datos de un usuario existente
     * @return bool
     */
    public function actualizarUsuario($idUsuario, $datosUsuario)
    {
        $sql = "UPDATE usuarios 
                SET id_rol = ?, email = ?, usuario_usuario = ?, nombre = ?, 
                    apellido_paterno = ?, apellido_materno = ?, telefono = ?, activo = ?
                WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("issssssii",
            $datosUsuario['id_rol'],
            $datosUsuario['email'],
            $datosUsuario['usuario_usuario'],
            $datosUsuario['nombre'],
            $datosUsuario['apellido_paterno'],
            $datosUsuario['apellido_materno'],
            $datosUsuario['telefono'],
            $datosUsuario['activo'],
            $idUsuario
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina un usuario del sistema
     * @return bool
     */
    public function eliminarUsuario($idUsuario)
    {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
} // Fin de la clase UsuarioDAO
?>