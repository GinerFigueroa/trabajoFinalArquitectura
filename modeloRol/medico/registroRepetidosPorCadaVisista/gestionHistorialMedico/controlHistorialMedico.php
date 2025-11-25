<?php

include_once('../../../../modelo/RegistroMedicoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// =====================================================================
// PATRÓN COMMAND: Interfaz y Clases Concretas
// =====================================================================

interface HistorialCommand
{
    // Método Abstracto: ejecutar
    public function ejecutar(); 
}

class EliminarRegistroCommand implements HistorialCommand
{
    // Atributos: $receptor, $idRegistro
    private controlHistorialClinico $receptor;
    private int $idRegistro;

    // Método: Constructor
    public function __construct(controlHistorialClinico $receptor, int $idRegistro)
    {
        $this->receptor = $receptor;
        $this->idRegistro = $idRegistro;
    }

    // Metodo: ejecutar
    public function ejecutar()
    {
        // El comando llama al método del Receptor para realizar la acción
        $this->receptor->ejecutarEliminacion($this->idRegistro);
    }
}

// =====================================================================
// PATRÓN FACTORY METHOD: Creación de comandos
// =====================================================================

class HistorialCommandFactory
{
    // Metodo: crearComando
    public static function crearComando(string $action, controlHistorialClinico $receptor, array $params): HistorialCommand
    {
        if ($action === 'eliminar' && isset($params['reg_id'])) {
            // Se valida que el parámetro necesario exista
            $idRegistro = (int)$params['reg_id'];
            if ($idRegistro <= 0) {
                 throw new Exception("ID de Registro no válido para la eliminación.");
            }
            return new EliminarRegistroCommand($receptor, $idRegistro);
        }
        
        throw new Exception("Acción de comando no válida: {$action}");
    }
}

class controlHistorialClinico // (Controlador y Receptor Command)
{
    // Atributo: $objDAO
    private $objDAO;
    // Atributo: $objMensaje
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objDAO = new RegistroMedicoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * RECEPTOR COMMAND: Lógica de negocio real para la eliminación.
     * Este método es llamado por el objeto Command.
     */
    // Metodo: ejecutarEliminacion
    public function ejecutarEliminacion(int $id_registro)
    {
        // 1. Ejecutar la eliminación
        $resultado = $this->objDAO->eliminarRegistro($id_registro);
        
        // 2. Manejo de resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Registro médico eliminado correctamente.', 
                './indexHistorialMedico.php', 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al eliminar el registro médico. Podría no existir.', 
                './indexHistorialMedico.php', 
                'error'
            );
        }
    }
}
?>
