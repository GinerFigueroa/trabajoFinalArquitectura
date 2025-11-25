<?php

include_once('../../../../../../modelo/misCitasDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

// =====================================================================
// PATRÓN COMMAND: Interfaz y Clases Concretas
// =====================================================================

interface CitaCommand 
{
    // Método Abstracto: ejecutar
    public function ejecutar(); 
}

class ConfirmarCitaCommand implements CitaCommand 
{
    private $idCita; 
    // Atributo: $receptor (El Mediator es también el Receptor/Servicio)
    private controlCitas $receptor; 

    // Método: Constructor
    public function __construct($idCita, controlCitas $receptor) 
    {
        $this->idCita = $idCita;
        $this->receptor = $receptor;
    }

    // Metodo: ejecutar
    public function ejecutar() 
    {
        // Llama al método Receptor/Mediator
        $this->receptor->procesarAccionCita($this->idCita, 'Confirmada');
    }
}

class CancelarCitaCommand implements CitaCommand 
{
    private $idCita;
    // Atributo: $receptor
    private controlCitas $receptor; 

    // Método: Constructor
    public function __construct($idCita, controlCitas $receptor) 
    {
        $this->idCita = $idCita;
        $this->receptor = $receptor;
    }

    // Metodo: ejecutar
    public function ejecutar() 
    {
        $this->receptor->procesarAccionCita($this->idCita, 'Cancelada');
    }
}

// =====================================================================
// PATRÓN STATE: Interfaz y Clases Concretas
// =====================================================================

interface EstadoCita 
{
    // Metodo Abstracto: obtenerClaseCSS
    public function obtenerClaseCSS(): string; 
}

class PendienteState implements EstadoCita 
{
    public function obtenerClaseCSS(): string { return 'warning'; }
}

class ConfirmadaState implements EstadoCita 
{
    public function obtenerClaseCSS(): string { return 'success'; }
}

class CompletadaState implements EstadoCita 
{
    public function obtenerClaseCSS(): string { return 'info'; }
}

class CanceladaState implements EstadoCita 
{
    public function obtenerClaseCSS(): string { return 'danger'; }
}

// =====================================================================
// PATRÓN FACTORY METHOD: Creación de objetos Command y State
// =====================================================================

class CitasFactory
{
    // Metodo: crearComando (Factory Method para Commands)
    public static function crearComando(string $action, int $idCita, controlCitas $control): CitaCommand
    {
        if ($action === 'confirmar') {
            return new ConfirmarCitaCommand($idCita, $control);
        }
        if ($action === 'cancelar') {
            return new CancelarCitaCommand($idCita, $control);
        }
        throw new Exception("Acción de comando no válida: {$action}");
    }

    // Metodo: crearEstado (Factory Method para States)
    public static function crearEstado(string $estado): EstadoCita
    {
        switch ($estado) {
            case 'Pendiente': return new PendienteState();
            case 'Confirmada': return new ConfirmadaState();
            case 'Completada': return new CompletadaState();
            case 'Cancelada': return new CanceladaState();
            default: return new PendienteState(); 
        }
    }
}


class controlCitas // (Controlador - Contexto State, Mediator, Receptor Command)
{
    // Atributo: $objCitas (Colaborador/Modelo)
    private $objCitas; 
    // Atributo: $objMensaje (Colaborador/Mensaje)
    private $objMensaje; 

    // Método: Constructor
    public function __construct()
    {
        $this->objCitas = new MisCitasDAO();
        $this->objMensaje = new mensajeSistema();
    }
    
    // PATRÓN MEDIATOR / RECEPTOR COMMAND: Método central de coordinación
    // Metodo: procesarAccionCita
    public function procesarAccionCita($idCita, $nuevoEstado)
    {
        // 1. Coordinación con el Modelo
        $resultado = $this->objCitas->actualizarEstadoCita($idCita, $nuevoEstado); 
        
        // 2. Coordinación con la Vista/Mensajes (STATE: Resultado de la operación)
        $urlRetorno = "./indexCita.php";
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita {$nuevoEstado} correctamente.", $urlRetorno, "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al {$nuevoEstado} la cita.", $urlRetorno, "error");
        }
    }

    // PATRÓN STATE: Método Contexto para obtener el estilo
    // Metodo: obtenerObjetoEstado
    public function obtenerObjetoEstado(string $estado): EstadoCita
    {
        // Usa el Factory para obtener el Estado
        return CitasFactory::crearEstado($estado);
    }
}
?>