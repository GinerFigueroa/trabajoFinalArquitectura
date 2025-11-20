<?php

// ==========================================================
// 1. INCLUSIONES (DEBEN IR PRIMERO)
// ==========================================================

// La inclusión de CitasDAO.php también debe proveer la clase EntidadesDAO (o inclúyela aparte)
include_once('../../../../modelo/CitasDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 2. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD y FACTORY METHOD 🏭
class Cita {
    // ... propiedades
    public $idPaciente; public $idTratamiento; public $idMedico; 
    public $fechaHora; public $duracion; public $estado; 
    public $notas; public $creadoPor;

    public function __construct(array $data) {
        // ... inicialización de propiedades
        $this->idPaciente = $data['idPaciente'] ?? null;
        $this->idTratamiento = $data['idTratamiento'] ?? null;
        $this->idMedico = $data['idMedico'] ?? null;
        $this->fechaHora = $data['fechaHora'] ?? null;
        $this->duracion = $data['duracion'] ?? 30;
        $this->estado = $data['estado'] ?? 'Pendiente';
        $this->notas = $data['notas'] ?? '';
        $this->creadoPor = $data['creadoPor'] ?? null;
    }
}

class CitasFactory {
    public static function crearCita(array $data): Cita {
        return new Cita($data);
    }
}

// CHAIN OF RESPONSIBILITY (Manejadores de Validación) 🔗
abstract class AbstractValidatorHandler {
    // ... (Métodos setNext, handle)
    private $nextHandler = null;
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }
    public function handle(Cita $cita): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($cita);
        }
        return null; 
    }
}

class CamposObligatoriosValidator extends AbstractValidatorHandler { /* ... */ }
class RangosValidator extends AbstractValidatorHandler { /* ... */ }

class EntidadesValidator extends AbstractValidatorHandler
{
    private $objEntidad;
    // La clase EntidadesDAO debe estar definida antes de este punto
    public function __construct() { $this->objEntidad = new EntidadesDAO(); } 

    public function handle(Cita $cita): ?string
    {
        // ... (lógica de validación)
        if (!$this->objEntidad->pacienteExiste($cita->idPaciente)) { return "El paciente seleccionado no es válido."; }
        if (!$this->objEntidad->tratamientoExiste($cita->idTratamiento)) { return "El tratamiento seleccionado no es válido o está inactivo."; }
        if (!$this->objEntidad->medicoExiste($cita->idMedico)) { return "El médico seleccionado no es válido."; }
        return parent::handle($cita);
    }
}

class DisponibilidadValidator extends AbstractValidatorHandler
{
    private $objCita;
    // ESTA ES LA LÍNEA CLAVE: CitaDAO debe estar definida.
    // (Anteriormente línea 126 del código original)
    public function __construct() { $this->objCita = new CitasDAO(); } 

    public function handle(Cita $cita): ?string
    {
        // ... (lógica de validación)
        if ($this->objCita->validarDisponibilidadMedico($cita->idMedico, $cita->fechaHora, $cita->duracion)) {
            return "El médico ya tiene una cita 'Pendiente' o 'Confirmada' en ese horario.";
        }
        return parent::handle($cita);
    }
}

// COMMAND (Lógica de Ejecución) 📦
interface Command {
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

class AgregarNuevaCitaCommand implements Command
{
    private $objCitaDAO;
    private $cita;
    private $validationChain;
    private $validationMessage = null;

    public function __construct(array $citaData)
    {
        // 🚨 La instancia de CitaDAO ahora funciona porque está definida arriba.
        $this->objCitaDAO = new CitasDAO(); 
        $this->cita = CitasFactory::crearCita($citaData);
        $this->buildValidationChain();
    }
    
    // ... (resto de los métodos)
    private function buildValidationChain()
    {
        $h1 = new CamposObligatoriosValidator();
        $h2 = new RangosValidator();
        $h3 = new EntidadesValidator();
        $h4 = new DisponibilidadValidator();
        $h1->setNext($h2)->setNext($h3)->setNext($h4);
        $this->validationChain = $h1;
    }

    public function execute(): bool
    {
        // ... (lógica de ejecución)
        $this->validationMessage = $this->validationChain->handle($this->cita);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        return $this->objCitaDAO->registrarCita(
            $this->cita->idPaciente, 
            $this->cita->idTratamiento, 
            $this->cita->idMedico, 
            $this->cita->fechaHora, 
            $this->cita->duracion, 
            $this->cita->estado, 
            $this->cita->notas, 
            $this->cita->creadoPor
        );
    }

    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 3. CONTROLADOR (MEDIATOR)
// ==========================================================

class controlAgregarNuevaCita
{
    // ... (métodos y propiedades)
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    public function agregarNuevaCita($idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas, $creadoPor)
    {
        $citaData = [
            'idPaciente' => $idPaciente, 'idTratamiento' => $idTratamiento, 'idMedico' => $idMedico,
            'fechaHora' => $fechaHora, 'duracion' => $duracion, 'estado' => $estado,
            'notas' => $notas, 'creadoPor' => $creadoPor
        ];

        // Se invoca al Command
        $command = new AgregarNuevaCitaCommand($citaData);
        $resultado = $command->execute();

        // Lógica del Mediator para manejar la respuesta
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            $this->objMensaje->mensajeSistemaShow($mensajeError, './indexAgregarNuevaCita.php', 'systemOut', false);
        } elseif ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Cita programada correctamente.', '../indexCita.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al programar la cita. Intente de nuevo.', './indexAgregarNuevaCita.php', 'error');
        }
    }
}
?>