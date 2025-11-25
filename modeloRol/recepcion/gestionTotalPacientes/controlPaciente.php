<?php

include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/PacienteDAO.php'); 

// ==========================================================
// 1. PATRÓN STRATEGY 🧩 (Estrategia de la Acción)
// ==========================================================

interface IAccionPacienteStrategy 
{
    // El método de la estrategia necesita las dependencias para operar
    public function execute(array $data, PacienteDAO $dao, mensajeSistema $mensaje): void;
}

// ----------------------------------------------------------
// ESTRATEGIAS CONCRETAS
// ----------------------------------------------------------

/**
 * Estrategia Concreta: Eliminar (Utiliza la Cadena de Responsabilidad para la decisión)
 */
class EliminarPacienteStrategy implements IAccionPacienteStrategy 
{
    // Atributo: `$handlerChain` (Referencia al inicio de la Cadena)
    private $handlerChain;

    public function __construct(IHandler $handlerChain) 
    {
        $this->handlerChain = $handlerChain;
    }

    public function execute(array $data, PacienteDAO $dao, mensajeSistema $mensaje): void
    {
        // Delega la ejecución a la Cadena
        $this->handlerChain->handle($data, $dao, $mensaje);
    }
}

/**
 * Estrategia Concreta: Reactivar Paciente (Transición de Estado)
 */
class ReactivarPacienteStrategy implements IAccionPacienteStrategy 
{
    public function execute(array $data, PacienteDAO $dao, mensajeSistema $mensaje): void
    {
        $idPaciente = $data['idPaciente'];
        $resultado = $dao->reactivarPaciente($idPaciente);

        if ($resultado) {
            $mensaje->mensajeSistemaShow("✅ Paciente reactivado correctamente.", "./indexTotalPaciente.php", "success");
        } else {
            $mensaje->mensajeSistemaShow("❌ Error al reactivar el paciente.", "./indexTotalPaciente.php", "error");
        }
    }
}

/**
 * Estrategia Concreta: Desactivar Paciente (Transición de Estado)
 */
class DesactivarPacienteStrategy implements IAccionPacienteStrategy 
{
    public function execute(array $data, PacienteDAO $dao, mensajeSistema $mensaje): void
    {
        $idPaciente = $data['idPaciente'];
        $resultado = $dao->desactivarPaciente($idPaciente);

        if ($resultado) {
            $mensaje->mensajeSistemaShow("✅ Paciente desactivado correctamente.", "./indexTotalPaciente.php", "success");
        } else {
            $mensaje->mensajeSistemaShow("❌ Error al desactivar el paciente.", "./indexTotalPaciente.php", "error");
        }
    }
}

// ==========================================================
// 2. PATRÓN CHAIN OF RESPONSIBILITY (HANDLER) 🔗
// ==========================================================

interface IHandler 
{
    public function setNext(IHandler $handler): IHandler;
    public function handle(array $data, PacienteDAO $dao, mensajeSistema $mensaje): ?bool;
}

abstract class AbstractHandler implements IHandler
{
    private $nextHandler = null;

    public function setNext(IHandler $handler): IHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle(array $data, PacienteDAO $dao, mensajeSistema $mensaje): ?bool
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($data, $dao, $mensaje);
        }
        return null;
    }
}

// ----------------------------------------------------------
// HANDLERS CONCRETOS (Para la Estrategia de 'Eliminar')
// ----------------------------------------------------------

/**
 * Handler 1: Intenta la Eliminación Física (solo si no hay historial).
 */
class EliminacionFisicaHandler extends AbstractHandler
{
    public function handle(array $data, PacienteDAO $dao, mensajeSistema $mensaje): ?bool
    {
        $idPaciente = $data['idPaciente'];
        
        // Asumiendo que esta función retorna: 
        // 1. ['success' => true, 'action' => 'deleted'] si se eliminó.
        // 2. ['success' => false, 'action' => 'deactivated_required'] si se necesita desactivar.
        $resultado = $dao->eliminarPacienteSiEsPosible($idPaciente);

        if ($resultado['success'] && $resultado['action'] === 'deleted') {
            $mensaje->mensajeSistemaShow("✅ Paciente eliminado completamente.", "./indexTotalPaciente.php", "success");
            return true; // Éxito y fin de la cadena
        }

        // Si falló la eliminación física, se pasa al siguiente Handler
        return parent::handle($data, $dao, $mensaje);
    }
}

/**
 * Handler 2: Propone la Desactivación (Gestiona la transición de Estado).
 */
class ProponerDesactivacionHandler extends AbstractHandler
{
    public function handle(array $data, PacienteDAO $dao, mensajeSistema $mensaje): ?bool
    {
        $idPaciente = $data['idPaciente'];
        $urlRetorno = "./indexTotalPaciente.php";

        // Lógica de Estado: Mostrar advertencia y preguntar la transición
        $mensajeHTML = "⚠️ Paciente con historial. No se puede eliminar. ¿Desea desactivarlo en su lugar?";
        
        // Se utiliza JS para gestionar la siguiente acción del usuario (transición de STATE)
        echo "<script>
                if (confirm('" . $mensajeHTML . "')) { 
                    // Redirige al Front Controller para ejecutar la estrategia 'desactivar'
                    window.location.href = './getPaciente.php?action=desactivar&id=" . $idPaciente . "';
                } else {
                    window.location.href = '" . $urlRetorno . "';
                }
            </script>";
        return true; // Se gestionó el flujo, fin de la cadena.
    }
}

// ==========================================================
// 3. CONTEXTO / MEDIATOR (Controlador) 🤝
// ==========================================================

/**
 * Clase controlPaciente (PATRÓN: CONTEXTO/MEDIATOR) 🤝
 */
class controlPaciente
{
    private $objPacienteDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objPacienteDAO = new PacienteDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    // Método: `crearEstrategia` (Factory implícito)
    private function crearEstrategia(string $action): IAccionPacienteStrategy 
    {
        switch ($action) {
            case 'eliminar':
                // Configura la CADENA DE RESPONSABILIDAD
                $eliminacionHandler = new EliminacionFisicaHandler();
                $proponerHandler = new ProponerDesactivacionHandler();
                
                // Enlaza la cadena: Física -> Proponer Desactivación
                $eliminacionHandler->setNext($proponerHandler); 
                
                // Retorna la Estrategia, inyectando el Handler inicial
                return new EliminarPacienteStrategy($eliminacionHandler); 
            
            case 'reactivar':
                return new ReactivarPacienteStrategy();
                
            case 'desactivar':
                return new DesactivarPacienteStrategy();

            default:
                throw new Exception("Acción de estrategia no soportada: " . $action);
        }
    }

    /**
     * Método: `procesarAccion` (Punto de entrada unificado / Contexto)
     * Utiliza el patrón Strategy.
     */
    public function procesarAccion(array $data): void
    {
        $action = $data['action'];
        $idPaciente = $data['idPaciente'];

        if (!is_numeric($idPaciente) || $idPaciente <= 0) {
             throw new Exception("ID de paciente no válido.");
        }

        // 1. Contexto: Obtiene la estrategia adecuada
        $estrategia = $this->crearEstrategia($action);

        // 2. Contexto: Ejecuta la Estrategia
        $estrategia->execute($data, $this->objPacienteDAO, $this->objMensaje);
    }
    
    // ==========================================================
    // MÉTODOS DE COMPATIBILIDAD (FACADE)
    // ==========================================================
    
    /**
     * @deprecated Utilice procesarAccion(['action' => 'eliminar', 'idPaciente' => $idPaciente])
     */
    public function eliminarPaciente($idPaciente) {
        $this->procesarAccion(['action' => 'eliminar', 'idPaciente' => $idPaciente]);
    }

    /**
     * @deprecated Utilice procesarAccion(['action' => 'desactivar', 'idPaciente' => $idPaciente])
     */
    public function desactivarPaciente($idPaciente) {
        $this->procesarAccion(['action' => 'desactivar', 'idPaciente' => $idPaciente]);
    }

    /**
     * @deprecated Utilice procesarAccion(['action' => 'reactivar', 'idPaciente' => $idPaciente])
     */
    public function reactivarPaciente($idPaciente) {
        $this->procesarAccion(['action' => 'reactivar', 'idPaciente' => $idPaciente]);
    }
}
?>