<?php

include_once('Conexion.php'); 

class UsuarioDAO
{
    private $connection;
    private $connectionProxy; // Emulación PROXY

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->connectionProxy = $this->connection; // El proxy es el objeto real aquí
    }

    private function getConnectionProxy() { return $this->connectionProxy; }

    public function obtenerTodosUsuarios()
    {
        $conn = $this->getConnectionProxy();
        $sql = "SELECT u.id_usuario, u.usuario_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.telefono, u.id_rol, u.activo, r.nombre as rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario DESC";

        $resultado = $conn->query($sql);
        $usuarios = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila;
            }
        }
        return $usuarios;
    }

    public function obtenerUsuarioPorId($idUsuario)
    {
        $conn = $this->getConnectionProxy();
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    public function validarCampoUnico($campo, $valor)
    {
        $conn = $this->getConnectionProxy();
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function validarCampoUnicoExcepto($campo, $valor, $idUsuario)
    {
        $conn = $this->getConnectionProxy();
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ? AND id_usuario != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $valor, $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function registrarUsuario($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo)
    {
        $conn = $this->getConnectionProxy();
        // $clave ya viene hasheada desde el controlador (FACTORY METHOD) o se hashea aquí.
        // Asumiendo que viene hasheada para simplificar el flujo COMMAND
        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, usuario_clave, telefono, usuario_usuario, id_rol, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssii", $nombre, $apellidoPaterno, $apellidoMaterno, $email, $clave, $telefono, $login, $idRol, $activo);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function editarUsuario($idUsuario, $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo)
    {
        $conn = $this->getConnectionProxy();
        if (!empty($clave)) {
            $sql = "UPDATE usuarios SET usuario_usuario = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, telefono = ?, usuario_clave = ?, id_rol = ?, activo = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssiii", $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo, $idUsuario);
        } else {
            $sql = "UPDATE usuarios SET usuario_usuario = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, telefono = ?, id_rol = ?, activo = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiiii", $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $idRol, $activo, $idUsuario);
        }
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function eliminarUsuario($idUsuario)
    {
        $conn = $this->getConnectionProxy();
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>