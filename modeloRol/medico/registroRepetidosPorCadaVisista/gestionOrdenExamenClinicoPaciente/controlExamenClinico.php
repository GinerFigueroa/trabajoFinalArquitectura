<?php

include_once('../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// =====================================================================
// PATRÓN COMMAND: Interfaz y Clases Concretas
// =====================================================================

interface OrdenExamenCommand 
{
    // Método Abstracto: ejecutar
    public function ejecutar(); 
}

class EliminarOrdenCommand implements OrdenExamenCommand 
{
    private $idOrden; 
    // Atributo: $receptor (El Mediator es también el Receptor)
    private controlExamenClinico $receptor; 

    // Método: Constructor
    public function __construct($idOrden, controlExamenClinico $receptor) 
    {
        $this->idOrden = $idOrden;
        $this->receptor = $receptor;
    }

    // Metodo: ejecutar
    public function ejecutar() 
    {
        // El comando llama al método del Receptor/Mediator
        $this->receptor->procesarAccionOrden($this->idOrden, 'eliminar');
    }
}

// =====================================================================
// PATRÓN CHAIN OF RESPONSIBILITY: Interfaz y Handlers Concretos
// =====================================================================

abstract class ValidationHandler 
{
    // Atributo Abstracto: $nextHandler (Siguiente en la cadena)
    protected $nextHandler = null; 

    // Método Abstracto: handle (Manejar la petición)
    abstract public function handle(int $idOrden): bool;

    // Metodo: setNext (Establecer el siguiente Handler)
    public function setNext(ValidationHandler $handler): ValidationHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }
}

class IdValidoHandler extends ValidationHandler
{
    // Metodo: handle
    public function handle(int $idOrden): bool 
    {
        if ($idOrden <= 0) {
            throw new Exception("Error de validación: ID de orden no válido.");
        }
        return $this->nextHandler ? $this->nextHandler->handle($idOrden) : true;
    }
}

class PermisoMedicoHandler extends ValidationHandler
{
    // Nota: Aquí iría la lógica de sesión/permisos, simplificada por el contexto.
    // Metodo: handle
    public function handle(int $idOrden): bool 
    {
        // Lógica real: if ($_SESSION['rol'] != 'Medico') return false;
        if (rand(0, 9) < 1) { // Simulación de fallo ocasional por permisos
             // throw new Exception("Error de permisos: No autorizado para eliminar la orden.");
        }
        return $this->nextHandler ? $this->nextHandler->handle($idOrden) : true;
    }
}

// =====================================================================
// PATRÓN STATE: Interfaz y Clases Concretas
// =====================================================================

interface EstadoOrden 
{
    // Metodo Abstracto: obtenerClaseCSS
    public function obtenerClaseCSS(): string; 
}

class PendienteState implements EstadoOrden 
{
    public function obtenerClaseCSS(): string { return 'warning'; }
}

class RealizadoState implements EstadoOrden 
{
    public function obtenerClaseCSS(): string { return 'success'; }
}

class EntregadoState implements EstadoOrden 
{
    public function obtenerClaseCSS(): string { return 'info'; }
}

// =====================================================================
// PATRÓN FACTORY METHOD: Creación de objetos Command y State
// =====================================================================

class ExamenFactory
{
    // Metodo: crearComando (Factory Method para Commands)
    public static function crearComando(string $action, int $idOrden, controlExamenClinico $control): OrdenExamenCommand
    {
        if ($action === 'eliminar') {
            return new EliminarOrdenCommand($idOrden, $control);
        }
        throw new Exception("Acción de comando no válida: {$action}");
    }

    // Metodo: crearEstado (Factory Method para States)
    public static function crearEstado(string $estado): EstadoOrden
    {
        switch ($estado) {
            case 'Pendiente': return new PendienteState();
            case 'Realizado': return new RealizadoState();
            case 'Entregado': return new EntregadoState();
            default: return new PendienteState(); 
        }
    }
}


class controlExamenClinico // (Controlador - Contexto State, Mediator, Receptor Command, Chain Starter)
{
    // Atributo: $objDAO (Colaborador/Modelo)
    private $objDAO; 
    // Atributo: $objMensaje (Colaborador/Mensaje)
    private $objMensaje; 

    // Método: Constructor
    public function __construct()
    {
        $this->objDAO = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
    }
    
    // PATRÓN MEDIATOR / RECEPTOR COMMAND: Método central de coordinación
    // Metodo: procesarAccionOrden
    public function procesarAccionOrden(int $idOrden, string $action)
    {
        $urlRetorno = "./indexOrdenExamenClinico.php";

        try {
            // PATRÓN CHAIN OF RESPONSIBILITY: Inicio de la cadena de validación
            $validadorID = new IdValidoHandler();
            $validadorPermiso = new PermisoMedicoHandler();
            
            // Atributo: $chain (Configuración de la cadena)
            $validadorID->setNext($validadorPermiso); 
            $validadorID->handle($idOrden); // Ejecutar la cadena

            // Si pasa la cadena, se ejecuta la acción (Eliminar en este caso)
            if ($action === 'eliminar') {
                $resultado = $this->objDAO->eliminarOrden($idOrden);
                if ($resultado) {
                    $this->objMensaje->mensajeSistemaShow('Orden de examen eliminada correctamente.', $urlRetorno, 'success');
                } else {
                    $this->objMensaje->mensajeSistemaShow('Error al eliminar la orden de examen.', $urlRetorno, 'error');
                }
            }

        } catch (Exception $e) {
            // Captura errores de la cadena (validación/permisos) o de la DAO
            $this->objMensaje->mensajeSistemaShow($e->getMessage(), $urlRetorno, 'error');
        }
    }

    // PATRÓN STATE: Método Contexto para obtener el estilo
    // Metodo: obtenerObjetoEstado
    public function obtenerObjetoEstado(string $estado): EstadoOrden
    {
        // Usa el Factory para obtener el Estado
        return ExamenFactory::crearEstado($estado);
    }
}
?>