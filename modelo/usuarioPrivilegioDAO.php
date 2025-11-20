<?php
/**
 * Patrón: DAO (Data Access Object)
 * Patrón: Singleton (Uso de la conexión global a través de 'conexion.php')
 * Responsabilidad: Acceder a los datos de Usuario relacionados con Roles/Privilegios.
 */
include_once('conexion.php'); // Asume que 'conexion.php' contiene la clase Conexion (Singleton)

class UsuarioPrivilegioDAO
{
    // Almacena el objeto mysqli obtenido del Singleton
    private $connection;
    
    /**
     * Patrón: Inyección de Dependencias (mediante Singleton)
     * Obtiene la única instancia de la conexión al crearse el DAO.
     */
    public function __construct() {
        // Se asume que 'Conexion' es la clase Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    /**
     * Obtiene los roles de un usuario específico.
     * @param string $login
     * @return array
     */
    public function obtenerPrivilegiosUsuario($login)
    {
        // NO es necesario llamar a conectarBD() ni desConectarBD()
        
        $sql = "SELECT r.nombre as rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.usuario_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $privilegios = array();
        while ($row = $resultado->fetch_assoc()) {
            $privilegios[] = $row;
        }
        
        $stmt->close();
        
        return $privilegios;
    }

    /**
     * Método adicional para obtener información completa del usuario con su rol
     * @param string $login
     * @return array|null
     */
    public function obtenerInformacionCompletaUsuario($login)
    {
        $sql = "SELECT u.*, r.nombre as rol, r.descripcion as descripcion_rol 
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
     * Patrón: Service Layer (Validación/Lógica de Negocio)
     * Este método contiene lógica de negocio (iteración sobre resultados),
     * aunque está en el DAO, es un buen candidato para moverlo a un Service Layer.
     * * @param string $login
     * @param string $rolBuscado
     * @return bool
     */
    public function usuarioTieneRol($login, $rolBuscado)
    {
        // Se delega al método DAO la obtención de datos
        $privilegios = $this->obtenerPrivilegiosUsuario($login);
        
        // Se realiza la lógica de negocio/validación
        foreach ($privilegios as $privilegio) {
            if ($privilegio['rol'] === $rolBuscado) {
                return true;
            }
        }
        
        return false;
    }
}
?>