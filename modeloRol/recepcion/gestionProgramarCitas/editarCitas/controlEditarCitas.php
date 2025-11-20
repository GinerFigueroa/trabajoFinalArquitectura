<?php
// Archivo: controlEditarCitas.php
include_once('../../../../modelo/CitasDAO.php'); // CitasDAO y EntidadesDAO
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Interfaces y Handlers)
// ==========================================================

abstract class CitaValidatorHandler {
    protected $nextHandler = null;
    protected $citaDAO;
    protected $entidadDAO;

    public function __construct(CitasDAO $cDao, EntidadesDAO $eDao) {
        $this->citaDAO = $cDao;
        $this->entidadDAO = $eDao;
    }

    public function setNext(CitaValidatorHandler $handler): CitaValidatorHandler {
        $this->nextHandler = $handler;
        return $handler;
    }
    abstract public function handle(array $datos): ?string;
    protected function checkNext(array $datos): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($datos);
        }
        return null;
    }
}

class RequiredFieldValidator extends CitaValidatorHandler {
    public function handle(array $datos): ?string {
        if (empty($datos['idCita']) || empty($datos['idPaciente']) || empty($datos['idTratamiento']) || empty($datos['idMedico']) || empty($datos['fechaHora']) || empty($datos['duracion']) || empty($datos['estado'])) {
            return 'Todos los campos marcados con (*) son obligatorios.';
        }
        return $this->checkNext($datos);
    }
}

class RangeAndTypeValidator extends CitaValidatorHandler {
    public function handle(array $datos): ?string {
        $duracion = $datos['duracion'];
        if (!is_numeric($duracion) || $duracion <= 0 || $duracion > 240) {
            return 'La duración debe ser un número positivo (máx 240 min).';
        }
        $estadosPermitidos = ['Pendiente', 'Confirmada', 'Completada', 'Cancelada', 'No asistió'];
        if (!in_array($datos['estado'], $estadosPermitidos)) {
            return 'Estado de cita no válido.';
        }
        return $this->checkNext($datos);
    }
}

class EntityExistenceValidator extends CitaValidatorHandler {
    public function handle(array $datos): ?string {
        if (!$this->entidadDAO->pacienteExiste($datos['idPaciente'])) {
            return "El paciente seleccionado no es válido.";
        }
        if (!$this->entidadDAO->tratamientoExiste($datos['idTratamiento'])) {
            return "El tratamiento seleccionado no es válido o está inactivo.";
        }
        if (!$this->entidadDAO->medicoExiste($datos['idMedico'])) {
            return "El médico seleccionado no es válido.";
        }
        return $this->checkNext($datos);
    }
}

class AvailabilityValidator extends CitaValidatorHandler {
    public function handle(array $datos): ?string {
        $estado = $datos['estado'];
        if ($estado !== 'Cancelada' && $estado !== 'Completada' && $this->citaDAO->validarDisponibilidadMedico($datos['idMedico'], $datos['fechaHora'], $datos['duracion'], $datos['idCita'])) {
            return "El médico ya tiene una cita 'Pendiente' o 'Confirmada' en ese horario.";
        }
        return $this->checkNext($datos);
    }
}

// ==========================================================
// PATRÓN: FACTORY METHOD (Para construir la cadena de validación)
// ==========================================================
class ValidatorFactory {
    public static function createCitaValidatorChain(CitasDAO $cDao, EntidadesDAO $eDao): CitaValidatorHandler {
        $required = new RequiredFieldValidator($cDao, $eDao);
        $rangeType = new RangeAndTypeValidator($cDao, $eDao);
        $existence = new EntityExistenceValidator($cDao, $eDao);
        $availability = new AvailabilityValidator($cDao, $eDao);

        // Construir la Cadena de Responsabilidad
        $required->setNext($rangeType)->setNext($existence)->setNext($availability);
        
        return $required;
    }
}


// ==========================================================
// PATRÓN: MEDIATOR / COMMAND (Lógica de Negocio)
// ==========================================================
class controlEditarCitas
{
    private $objCitaDAO;
    private $objEntidadDAO;
    private $objMensaje;
    private $validatorChain;

    public function __construct()
    {
        $this->objCitaDAO = new CitasDAO();
        $this->objEntidadDAO = new EntidadesDAO();
        $this->objMensaje = new mensajeSistema();
        
        // Inicializa la Chain of Responsibility usando el Factory Method
        $this->validatorChain = ValidatorFactory::createCitaValidatorChain($this->objCitaDAO, $this->objEntidadDAO);
    }

    /**
     * Implementa el patrón COMMAND y MEDIATOR.
     */
    public function editarCitaCommand(array $datos) // COMMAND
    {
        $redirectUrl = './indexEditarCitas.php?id=' . urlencode($datos['idCita']);

        // 1. MEDIATOR: Ejecutar la Cadena de Responsabilidad (Chain)
        $validationError = $this->validatorChain->handle($datos);

        if ($validationError) {
            $this->objMensaje->mensajeSistemaShow(
                $validationError, 
                $redirectUrl, 
                'systemOut', 
                false
            );
            return;
        }

        // 2. COMMAND: Ejecución de la Actualización (Delegación al DAO)
        $resultado = $this->objCitaDAO->editarCita(
            $datos['idCita'],
            $datos['idPaciente'],
            $datos['idTratamiento'],
            $datos['idMedico'],
            $datos['fechaHora'],
            $datos['duracion'],
            $datos['estado'],
            $datos['notas']
        );
        
        // 3. MEDIATOR: Manejo de la respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Cita editada correctamente.', '../indexCita.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar la cita. No se detectaron cambios o hubo un error en BD.', $redirectUrl, 'error');
        }
    }
}
?>