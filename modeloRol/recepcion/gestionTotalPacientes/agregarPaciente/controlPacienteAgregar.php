<?php
// C:\...\gestionTotalPacientes\agregarPaciente\controlPacienteAgregar.php
include_once('../../../../modelo/PacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Manejo de Validaciones)
// ==========================================================

// Interfaz/Clase Base para el Handler (Abstract Handler)
abstract class PacienteValidatorHandler {
    protected $nextHandler = null;
    protected $pacienteDAO;
    protected $entidadDAO;

    public function __construct(PacienteDAO $pDAO, EntidadPacienteDAO $eDAO) {
        $this->pacienteDAO = $pDAO;
        $this->entidadDAO = $eDAO;
    }

    public function setNext(PacienteValidatorHandler $handler): PacienteValidatorHandler {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método principal de la cadena: Retorna mensaje de error (string) o null (OK)
    abstract public function handle(array $datos): ?string;

    protected function checkNext(array $datos): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($datos);
        }
        return null; // Cadena finalizada, sin errores.
    }
}

// Handler 1: Validación de Existencia de Usuario
class UsuarioExistenteValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        if (!$this->entidadDAO->usuarioExiste($datos['idUsuario'])) {
            return "El ID de usuario seleccionado no es válido o no tiene el rol de paciente activo. (CHAIN: Usuario Inválido)";
        }
        // También validamos que el usuario NO tenga paciente asociado, como estaba en la lógica original.
        if ($this->pacienteDAO->obtenerPacientePorIdUsuario($datos['idUsuario'])) {
            return "El usuario ID **{$datos['idUsuario']}** ya tiene datos de paciente asociados. Seleccione otro usuario. (CHAIN: Paciente Existente)";
        }
        return $this->checkNext($datos);
    }
}

// Handler 2: Validación de Unicidad del DNI
class DniUnicidadValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        if ($this->pacienteDAO->dniExiste($datos['dni'])) {
            return "El DNI **{$datos['dni']}** ya está registrado en otro paciente. (CHAIN: DNI Duplicado)";
        }
        return $this->checkNext($datos);
    }
}

// Handler 3: Validación de Formato del DNI
class DniFormatoValidator extends PacienteValidatorHandler {
    public function handle(array $datos): ?string {
        if (!preg_match('/^[0-9A-Z]{6,15}$/', $datos['dni'])) {
            return "El formato del DNI es inválido. Debe contener solo números y/o letras, longitud 6-15. (CHAIN: Formato DNI)";
        }
        return $this->checkNext($datos);
    }
}

// Clase constructora de la Cadena
class PacienteValidatorChain {
    private $firstHandler;

    public function __construct(PacienteDAO $pDAO, EntidadPacienteDAO $eDAO) {
        // Inicializar y construir la Cadena de Responsabilidad
        $usuarioValidator = new UsuarioExistenteValidator($pDAO, $eDAO);
        $dniUnicidadValidator = new DniUnicidadValidator($pDAO, $eDAO);
        $dniFormatoValidator = new DniFormatoValidator($pDAO, $eDAO);
        
        $this->firstHandler = $usuarioValidator;
        $usuarioValidator->setNext($dniUnicidadValidator)->setNext($dniFormatoValidator);
    }

    public function validate(array $datos): ?string {
        return $this->firstHandler->handle($datos);
    }
}

// ==========================================================
// PATRÓN: MEDIATOR / COMMAND (Lógica de Negocio)
// ==========================================================
class controlPacienteAgregar // MEDIATOR
{
    private $objPacienteDAO; 
    private $objEntidadPacienteDAO;
    private $objMensaje;
    private $validatorChain;

    public function __construct()
    {
        $this->objPacienteDAO = new PacienteDAO(); 
        $this->objEntidadPacienteDAO = new EntidadPacienteDAO();
        $this->objMensaje = new mensajeSistema();
        // Inicializa la Chain of Responsibility
        $this->validatorChain = new PacienteValidatorChain($this->objPacienteDAO, $this->objEntidadPacienteDAO);
    }
    
    /**
     * Registra los datos detallados del paciente (COMMAND).
     */
    public function registrarPaciente($data) // Recibe el array del Builder
    {
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacionError = $this->validatorChain->validate($data);
        
        if ($validacionError !== null) {
            $this->objMensaje->mensajeSistemaShow($validacionError, './indexPacienteAgregar.php', 'systemOut', false);
            return;
        }

        // 2. Ejecución del COMMAND (Delegación al DAO)
        $resultado = $this->objPacienteDAO->registrarPaciente(
            $data['idUsuario'], 
            $data['fechaNacimiento'], 
            $data['lugarNacimiento'], 
            $data['ocupacion'], 
            $data['dni'], 
            $data['domicilio'], 
            $data['distrito'], 
            $data['edad'], 
            $data['sexo'], 
            $data['estadoCivil'], 
            $data['nombreApoderado'], 
            $data['apellidoPaternoApoderado'], 
            $data['apellidoMaternoApoderado'], 
            $data['parentescoApoderado']
        );

        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Datos detallados del paciente registrados correctamente. Ahora aparece en la lista principal.', 
                '../indexTotalPaciente.php', 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar los datos del paciente. Revise la conexión a la base de datos.', 
                './indexPacienteAgregar.php', 
                'error'
            );
        }
    }
}
?>