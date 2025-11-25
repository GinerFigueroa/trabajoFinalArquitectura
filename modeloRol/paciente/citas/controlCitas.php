<?php
// FILE: controlCitas.php

include_once('../../../modelo/citasPacientesDAO.php');
include_once('../../../shared/mensajeSistema.php');

// --- PATRÓN: MEDIATOR (Implementación) ---
class CitasMediator {
    private $dao; // ATRIBUTO: DAO (Colleague)
    private $mensaje; // ATRIBUTO: Mensaje (Colleague)

    public function __construct(CitasPacientesDAO $dao, mensajeSistema $mensaje) {
        $this->dao = $dao;
        $this->mensaje = $mensaje;
    }
    
    // MÉTODOS DE MEDIACIÓN
    public function obtenerIdPacientePorUsuario(int $idUsuario): ?int {
        return $this->dao->obtenerIdPacientePorUsuario($idUsuario);
    }

    public function verificarPropiedadCita(int $idCita, int $idPaciente): bool {
        return $this->dao->verificarPropiedadCita($idCita, $idPaciente);
    }
    
    public function cancelarCitaDAO(int $idCita, int $idPaciente): bool {
        // Lógica de acceso a datos mediada
        return $this->dao->cancelarCita($idCita, $idPaciente);
    }
    
    public function notificarError(string $msg): void {
        $this->mensaje->mensajeSistemaShow($msg, "./indexCitas.php", "error");
    }
    
    public function notificarExito(string $msg): void {
        $this->mensaje->mensajeSistemaShow($msg, "./indexCitas.php", "success");
    }
}
// --- FIN PATRÓN MEDIATOR ---


// --- PATRÓN: CHAIN OF RESPONSIBILITY (Implementación) ---
abstract class ValidadorBase {
    protected $siguiente = null; // ATRIBUTO: Siguiente handler en la cadena
    
    // MÉTODOS DE LA INTERFAZ
    public function establecerSiguiente(ValidadorBase $handler): ValidadorBase {
        $this->siguiente = $handler;
        return $handler;
    }

    abstract public function manejar(int $idCita, int $idPaciente, CitasMediator $mediator): ?bool;
}

class ValidadorPropiedadCita extends ValidadorBase {
    // MÉTODO: Ejecuta la validación y llama al siguiente si es exitosa
    public function manejar(int $idCita, int $idPaciente, CitasMediator $mediator): ?bool {
        if (!$mediator->verificarPropiedadCita($idCita, $idPaciente)) {
            $mediator->notificarError("No tiene permisos para cancelar esta cita.");
            return false;
        }
        return $this->siguiente ? $this->siguiente->manejar($idCita, $idPaciente, $mediator) : true;
    }
}
// Se pueden añadir más validadores aquí (ej: ValidadorEstadoCancelable, ValidadorTiempoLimite)
// --- FIN PATRÓN CHAIN OF RESPONSIBILITY ---


// --- PATRÓN: COMMAND (Implementación) ---
interface ComandoCita {
    // MÉTODO ABSTRACTO
    public function ejecutar(): bool;
}

class CancelarCitaCommand implements ComandoCita {
    private $mediator; // ATRIBUTO: Receptor de la acción
    private $idCita;
    private $idPaciente;

    public function __construct(CitasMediator $mediator, int $idCita, int $idPaciente) {
        $this->mediator = $mediator;
        $this->idCita = $idCita;
        $this->idPaciente = $idPaciente;
    }

    // MÉTODO CONCRETO: Implementa la acción
    public function ejecutar(): bool {
        // En un Command más complejo, se podría usar la Chain of Responsibility aquí.
        // Aquí la Chain of Responsibility ya está siendo invocada en el Invoker.
        return $this->mediator->cancelarCitaDAO($this->idCita, $this->idPaciente);
    }
}
// --- FIN PATRÓN COMMAND ---


class controlCitas
{
    private $mediator; // ATRIBUTO: Instancia del Mediator
    private $objCitas; // Mantenemos el DAO para compatibilidad con el código original, pero usaremos el mediator

    public function __construct()
    {
        $this->objCitas = new CitasPacientesDAO(); // Se mantiene el DAO
        $objMensaje = new mensajeSistema();
        $this->mediator = new CitasMediator($this->objCitas, $objMensaje);
    }

    /**
     * PATRÓN: CHAIN OF RESPONSIBILITY (Punto de entrada) y COMMAND (Invoker)
     */
    public function cancelarCita($idCita, $idUsuario)
    {
        $idPaciente = $this->mediator->obtenerIdPacientePorUsuario($idUsuario);
        
        if (!$idPaciente) {
            $this->mediator->notificarError("Error: No se pudo identificar al paciente.");
            return;
        }

        // 1. Configurar la Chain of Responsibility
        $validadorPropiedad = new ValidadorPropiedadCita();
        // $validadorPropiedad->establecerSiguiente(new ValidadorEstadoCancelable()); // Ejemplo de extensión
        
        // 2. Ejecutar la cadena
        if ($validadorPropiedad->manejar($idCita, $idPaciente, $this->mediator) === false) {
            return; // Falló la validación, el error ya fue notificado por el Mediator
        }

        // 3. Si la cadena es exitosa, ejecutar el Command
        $comandoCancelacion = new CancelarCitaCommand($this->mediator, $idCita, $idPaciente);
        $resultado = $comandoCancelacion->ejecutar(); // MÉTODO: ejecutar
        
        if ($resultado) {
            $this->mediator->notificarExito("Cita cancelada correctamente.");
        } else {
            $this->mediator->notificarError("Error al cancelar la cita. La cita no puede ser cancelada en su estado actual.");
        }
    }
}
?>