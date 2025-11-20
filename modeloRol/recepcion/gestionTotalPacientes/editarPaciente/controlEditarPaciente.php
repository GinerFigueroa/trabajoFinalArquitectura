<?php
// Archivo: controlEditarPaciente.php
include_once('../../../../modelo/pacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Manejo de Validaciones)
// ==========================================================

// Interfaz/Clase Base para el Handler
abstract class PacienteValidatorHandler {
    protected $nextHandler = null;
    protected $pacienteDAO;

    public function __construct(PacienteDAO $dao) {
        $this->pacienteDAO = $dao;
    }

    public function setNext(PacienteValidatorHandler $handler): PacienteValidatorHandler {
        $this->nextHandler = $handler;
        return $handler;
    }

    // El método central de la cadena.
    abstract public function handle(array $datos): ?string;

    // Ejecuta el siguiente validador si existe
    protected function checkNext(array $datos): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($datos);
        }
        return null; // Cadena finalizada, sin errores.
    }
}

class RequiredFieldValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        if (empty($datos['idPaciente']) || empty($datos['dni'])) {
            return 'El campo DNI es obligatorio.';
        }
        return $this->checkNext($datos);
    }
}

class DniFormatValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        // Validación solo de números
        if (!preg_match('/^[0-9]+$/', $datos['dni'])) {
            return 'El DNI debe contener solo números.';
        }
        return $this->checkNext($datos);
    }
}

class DniUniquenessValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        // Validación de unicidad, excluyendo el paciente actual
        if ($this->pacienteDAO->dniExiste($datos['dni'], $datos['idPaciente'])) {
            return "Ya existe un paciente con el DNI {$datos['dni']}.";
        }
        return $this->checkNext($datos);
    }
}

class AgeValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        $edad = $datos['edad'];
        if (!empty($edad) && (!is_numeric($edad) || $edad < 0 || $edad > 150)) {
            return 'La edad no es válida.';
        }
        return $this->checkNext($datos);
    }
}

class PacienteValidatorChain {
    private $firstHandler;

    public function __construct(PacienteDAO $dao) {
        // Inicializar y construir la Cadena: Define el orden de las validaciones
        $required = new RequiredFieldValidator($dao);
        $format = new DniFormatValidator($dao);
        $uniqueness = new DniUniquenessValidator($dao);
        $age = new AgeValidator($dao);

        $this->firstHandler = $required;
        $required->setNext($format)->setNext($uniqueness)->setNext($age);
    }

    public function validate(array $datos): ?string {
        return $this->firstHandler->handle($datos);
    }
}


// ==========================================================
// PATRÓN: MEDIATOR / COMMAND (Lógica de Negocio)
// ==========================================================
class controlEditarPaciente // MEDIATOR / COMMAND
{
    private $objPacienteDAO;
    private $objMensaje;
    private $validatorChain;

    public function __construct()
    {
        $this->objPacienteDAO = new PacienteDAO();
        $this->objMensaje = new mensajeSistema();
        // Inicializa la Chain of Responsibility
        $this->validatorChain = new PacienteValidatorChain($this->objPacienteDAO);
    }

    /**
     * Implementa el patrón COMMAND y MEDIATOR.
     */
    public function editarPaciente($datos)
    {
        $redirectUrl = './indexEditarPaciente.php?id=' . urlencode($datos['idPaciente']);

        // 1. MEDIATOR: Ejecutar la Cadena de Responsabilidad (Chain)
        $validationError = $this->validatorChain->validate($datos);

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
        $resultado = $this->objPacienteDAO->editarPaciente(
            $datos['idPaciente'],
            $datos['fechaNacimiento'],
            $datos['lugarNacimiento'],
            $datos['ocupacion'],
            $datos['dni'],
            $datos['domicilio'],
            $datos['distrito'],
            $datos['edad'],
            $datos['sexo'],
            $datos['estadoCivil'],
            $datos['nombreApoderado'],
            $datos['apellidoPaternoApoderado'],
            $datos['apellidoMaternoApoderado'],
            $datos['parentescoApoderado']
        );
        
        // 3. MEDIATOR: Manejo de la respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Paciente editado correctamente.', '../indexTotalPaciente.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar el paciente. No se detectaron cambios o hubo un error en BD.', $redirectUrl, 'error');
        }
    }
}
?>