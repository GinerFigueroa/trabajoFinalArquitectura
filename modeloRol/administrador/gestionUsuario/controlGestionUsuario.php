<?php

include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/UsuarioDAO.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: COMMAND Y FACTORY
// ==========================================================

// Interfaz del Patrón COMMAND 📦
interface UsuarioCommand {
    /**
     * @return array Retorna ['success' => bool, 'message' => string] o ['redirection' => bool]
     */
    public function execute(): array;
}

// Implementación COMMAND: Reactivar Usuario 
class ReactivarUsuarioCommand implements UsuarioCommand {
    private $objDAO;
    private $idUsuario;

    public function __construct($idUsuario) {
        $this->objDAO = new UsuarioDAO();
        $this->idUsuario = (int)$idUsuario;
    }

    public function execute(): array {
        if ($this->idUsuario <= 0) {
            return ['success' => false, 'message' => "ID de usuario no válido."];
        }
        
        $resultado = $this->objDAO->reactivarUsuario($this->idUsuario);
        
        if ($resultado) {
            return ['success' => true, 'message' => "Usuario reactivado correctamente."];
        } else {
            return ['success' => false, 'message' => "Error al reactivar el usuario. Consulte logs."];
        }
    }
}

// Implementación COMMAND: Desactivar Usuario
class DesactivarUsuarioCommand implements UsuarioCommand {
    private $objDAO;
    private $idUsuario;

    public function __construct($idUsuario) {
        $this->objDAO = new UsuarioDAO();
        $this->idUsuario = (int)$idUsuario;
    }

    public function execute(): array {
        if ($this->idUsuario <= 0) {
            return ['success' => false, 'message' => "ID de usuario no válido."];
        }
        
        $resultado = $this->objDAO->desactivarUsuario($this->idUsuario);
        
        if ($resultado) {
            return ['success' => true, 'message' => "Usuario desactivado correctamente."];
        } else {
            return ['success' => false, 'message' => "Error al desactivar el usuario. Consulte logs."];
        }
    }
}

// Implementación COMMAND: Eliminar Usuario
class EliminarUsuarioCommand implements UsuarioCommand {
    private $objDAO;
    private $idUsuario;

    public function __construct($idUsuario) {
        $this->objDAO = new UsuarioDAO();
        $this->idUsuario = (int)$idUsuario;
    }

    public function execute(): array {
        if ($this->idUsuario <= 0) {
            return ['success' => false, 'message' => "ID de usuario no válido."];
        }

        $resultado = $this->objDAO->eliminarUsuarioSiEsPosible($this->idUsuario);
        
        if ($resultado['success']) {
            // Éxito: El usuario fue eliminado o desactivado (según la lógica del DAO)
            return ['success' => true, 'message' => $resultado['message']];
        } else {
            // Fallo: El DAO indicó que no se puede eliminar por relaciones. Ofrecemos la opción de desactivar.
            $mensaje = $resultado['message'] . ". ¿Desea desactivarlo en su lugar?";
            
            // Nota: En un entorno sin alert/confirm, esto idealmente se manejaría
            // con un modal JS o redireccionando con un flag. Para mantener la 
            // funcionalidad original de confirm, se mantiene el script.
            echo "<script>
                // La variable 'desactivar_url' es una URL de acción específica
                const desactivar_url = './getGestionUsuario.php?action=desactivar&id=" . $this->idUsuario . "';
                if (confirm('" . $mensaje . "')) {
                    window.location.href = desactivar_url;
                } else {
                    window.location.href = './indexGestionUsuario.php';
                }
            </script>";
            // Retorna un flag de redirección para evitar que el Mediator siga procesando.
            return ['redirection' => true];
        }
    }
}

// Patrón: COMMAND FACTORY 🏭
class UsuarioCommandFactory {
    
    /**
     * Crea una instancia del comando concreto basado en la acción.
     * @throws Exception Si la acción no es soportada.
     */
    public static function crearComando(string $action, int $idUsuario): UsuarioCommand {
        switch ($action) {
            case 'eliminar':
                return new EliminarUsuarioCommand($idUsuario);
            case 'desactivar':
                return new DesactivarUsuarioCommand($idUsuario);
            case 'reactivar':
                return new ReactivarUsuarioCommand($idUsuario);
            default:
                throw new Exception("Acción de gestión de usuario no soportada: {$action}");
        }
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre la creación del Command (Factory), 
 * la ejecución del Command y el manejo de los mensajes de sistema.
 */
class controlGestionUsuario
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Método: `ejecutarAccionUsuario` (Punto de coordinación central)
     * @param string $action La acción a ejecutar (ej. 'eliminar', 'desactivar', 'reactivar').
     * @param int $idUsuario El ID del usuario afectado.
     */
    public function ejecutarAccionUsuario(string $action, int $idUsuario)
    {
        $urlRetorno = "./indexGestionUsuario.php";

        try {
            // Factory Method: Creación del COMMAND
            $command = UsuarioCommandFactory::crearComando($action, $idUsuario);

            // Command: Ejecución
            $resultado = $command->execute();

            // Lógica para manejar el resultado del Command
            if (isset($resultado['redirection'])) {
                // Si el comando solicitó una redirección (ej. en caso de confirmar desactivación), 
                // el script ya ejecutó el JS y no hacemos nada más.
                return; 
            }
            
            if ($resultado['success']) {
                // Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ " . $resultado['message'], 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Fallo
                $this->objMensaje->mensajeSistemaShow(
                    "❌ " . $resultado['message'], 
                    $urlRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Error de fábrica o interno inesperado
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error'
            );
        }
    }
}
?>