<?php
// Asegúrate de que este include apunte a tu clase Conexion (Singleton)
include_once('conexion.php');

/**
 * Patrón: DAO (Data Access Object)
 * Clase RolDAO - Responsable única de la persistencia de la entidad 'roles'.
 */
class RolDAO
{
    // Almacena el objeto mysqli obtenido del Singleton
    private $connection;

    /**
     * Patrón: Inyección de Dependencias (mediante Singleton)
     * Obtiene la única instancia de la conexión al crearse el DAO.
     */
    public function __construct()
    {
        // Se obtiene la conexión viva del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todos los roles de la base de datos.
     * @return array
     */
    public function obtenerTodosRoles()
    {
        // NO es necesario llamar a conectarBD() ni desConectarBD()
        
        $sql = "SELECT id_rol, nombre FROM roles";
        
        // Se usa el objeto de conexión directamente
        $respuesta = $this->connection->query($sql); 
        
        $roles = [];
        if ($respuesta) {
             while ($row = $respuesta->fetch_assoc()) {
                $roles[] = $row;
            }
        }
       
        return $roles;
    }

    /**
     * Obtiene un rol específico por su ID.
     * @param int $idRol
     * @return array|null
     */
    public function obtenerRolPorId($idRol)
    {
        $sql = "SELECT * FROM roles WHERE id_rol = ?";
        // Se usa el objeto de conexión directamente
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bind_param("i", $idRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $rol = $resultado->fetch_assoc();
        
        $stmt->close();
        
        return $rol;
    }

    /**
     * Obtiene los permisos de un rol específico.
     * @param int $idRol
     * @return array
     */
    public function obtenerPermisosPorRol($idRol)
    {
        $sql = "SELECT p.* FROM permisos p 
                 INNER JOIN rol_permisos rp ON p.id_permiso = rp.id_permiso 
                 WHERE rp.id_rol = ?";
        
        // Se usa el objeto de conexión directamente
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bind_param("i", $idRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $permisos = [];
        while ($row = $resultado->fetch_assoc()) {
            $permisos[] = $row;
        }
        
        $stmt->close();
        
        return $permisos;
    }
}
?>