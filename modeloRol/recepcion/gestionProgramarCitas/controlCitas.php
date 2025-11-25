<?php
// Archivo: controlCitas.php

// Inclusiones de dependencias
include_once('../../../modelo/CitasDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Manejo de Solicitudes)
// ==========================================================

// Atributos Abstractos: Ninguno
// Método Abstracto: handle(array $request)
abstract class SolicitudHandler {
    protected $siguienteHandler;
    protected $objMensaje;

    public function __construct() {
        $this->objMensaje = new mensajeSistema();
    }

    public function setNext(SolicitudHandler $handler): SolicitudHandler {
        $this->siguienteHandler = $handler;
        return $handler;
    }

    abstract public function handle(array $request);
}

// ----------------------------------------------------------
// Paso 1: Valida que el ID esté presente y sea un número
// ----------------------------------------------------------
class ValidarIdHandler extends SolicitudHandler {
    // Ejemplo Método: handle(array $request)
    public function handle(array $request) {
        if (!isset($request['id']) || !is_numeric($request['id']) || (int)$request['id'] <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de cita no válido o no proporcionado.", "./indexCita.php", "systemOut", false);
            return null; // Detiene la cadena
        }
        return $this->siguienteHandler ? $this->siguienteHandler->handle($request) : $request;
    }
}

// ----------------------------------------------------------
// Paso 2: Ejecutar la acción
// ----------------------------------------------------------
class EjecutarEliminacionHandler extends SolicitudHandler {
    private $objCitaDAO;

    public function __construct() {
        parent::__construct();
        // PATRÓN FACTORY METHOD (Sencillo): Fábrica de DAO
        $this->objCitaDAO = $this->createCitasDAO(); 
    }

    // Ejemplo Método: createCitasDAO() (Implementación del Factory Method)
    private function createCitasDAO(): CitasDAO {
        return new CitasDAO();
    }

    // Ejemplo Método: handle(array $request)
    public function handle(array $request) {
        $idCita = (int)$request['id'];

        // Delegación al DAO (El DAO actúa como el Receptor de la acción)
        $resultado = $this->objCitaDAO->eliminarCita($idCita);
        
        // Manejo de la respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita eliminada correctamente.", "./indexCita.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la cita. Verifique si la cita existe.", "./indexCita.php", "error");
        }
        return null; // La acción finaliza aquí
    }
}

// ==========================================================
// PATRÓN: MEDIATOR (Coordinador)
// ==========================================================

/**
 * Clase controlCitas (PATRÓN: MEDIATOR) 🤝
 * Atributos: $chain, $objMensaje
 * Métodos: __construct(), eliminarCita(array $request)
 */
class controlCitas 
{
    private $chain;
    private $objMensaje;

    // Método: __construct()
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        // Configuración de la cadena de responsabilidad
        $validarId = new ValidarIdHandler();
        $ejecutarEliminacion = new EjecutarEliminacionHandler();
        
        // ⛓️ La cadena se establece: ID -> Ejecutar
        $validarId->setNext($ejecutarEliminacion);
        $this->chain = $validarId;
    }

    /**
     * Inicia la ejecución de la Cadena de Responsabilidad.
     * Ejemplo Método: eliminarCita(array $request)
     * @param array $request Contiene 'action' y 'id'.
     */
    public function eliminarCita(array $request)
    {
        // 🤝 El Mediator inicia la coordinación a través de la cadena
        $this->chain->handle($request);
    }
}
?>