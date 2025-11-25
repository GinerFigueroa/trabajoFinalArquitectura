<?php

include_once("../../../../modelo/ExamenClinicoDAO.php");
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class ExamenDTO {
    public $historiaClinicaId;
    public $peso;
    public $talla;
    public $pulso;
    public $idEnfermero;
    
    public function __construct(array $data) {
        // Limpieza y type casting de datos
        $this->historiaClinicaId = (int)($data['historia_clinica_id'] ?? 0);
        $this->peso = (float)($data['peso'] ?? 0.0);
        $this->talla = (float)($data['talla'] ?? 0.0);
        $this->pulso = trim((string)($data['pulso'] ?? ''));
        // El idEnfermero es opcional
        $this->idEnfermero = !empty($data['id_enfermero']) ? (int)$data['id_enfermero'] : NULL;
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class ExamenFactory {
    public static function crearDTO(array $data): ExamenDTO {
        // Crea y retorna el DTO
        return new ExamenDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, ExamenDTO $dto): Comando {
        switch ($action) {
            case 'registrar':
                // Crea y retorna el comando de registro
                return new RegistrarExamenCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada: " . $action);
        }
    }
}

// COMMAND Concreto: Registrar Examen 📦
class RegistrarExamenCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (ExamenClinicoDAO)
    private $dto;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    public function __construct(ExamenDTO $dto)
    {
        $this->objDAO = new ExamenClinicoDAO();
        $this->dto = $dto;
    }

    // Método de Validación (Mueve toda la lógica de validación del antiguo control)
    private function validate(): bool
    {
        // 1. Validaciones de Campo Vacío (Obligatorias)
        if ($this->dto->historiaClinicaId <= 0 || $this->dto->peso <= 0 || $this->dto->talla <= 0 || empty($this->dto->pulso)) {
            $this->validationMessage = "Faltan campos obligatorios (Paciente, Peso, Talla y Pulso).";
            return false;
        }

        // 2. Validaciones de Formato y Límite
        if (!is_numeric($this->dto->peso) || $this->dto->peso > 500) {
            $this->validationMessage = "El campo Peso debe ser un valor numérico positivo (máx 500).";
            return false;
        }
        if (!is_numeric($this->dto->talla) || $this->dto->talla > 3.0) {
            $this->validationMessage = "El campo Talla debe ser un valor numérico positivo (máx 3.0).";
            return false;
        }
        if (strlen($this->dto->pulso) > 20) {
            $this->validationMessage = "El campo Pulso no debe exceder los 20 caracteres.";
            return false;
        }

        // 3. Validaciones de Existencia de Entidades (Receptor/DAO)
        if (!$this->objDAO->obtenerNombrePacientePorHistoriaClinica($this->dto->historiaClinicaId)) {
            $this->validationMessage = "El ID de Historia Clínica seleccionado no es válido o no existe.";
            return false;
        }
        
        if ($this->dto->idEnfermero !== NULL && !$this->objDAO->obtenerNombrePersonalPorIdUsuario($this->dto->idEnfermero)) { 
            $this->validationMessage = "El ID de Enfermera/o seleccionado no es válido o no existe.";
            return false;
        }

        return true;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // Ejecuta la validación
        if (!$this->validate()) {
            return false; // Falla la ejecución si la validación falla
        }

        // Ejecución del receptor (DAO)
        return $this->objDAO->registrarExamen(
            $this->dto->historiaClinicaId, 
            $this->dto->peso, 
            $this->dto->talla, 
            $this->dto->pulso, 
            $this->dto->idEnfermero
        );
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre la creación del Command/DTO (Factory), 
 * la ejecución del Command y el manejo de los resultados (State).
 */
class controlExamenAgregar
{
    private $objMensaje;
    // Eliminamos la dependencia a ExamenClinicoDAO, ya que el Command la maneja.

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
     */
    public function ejecutarComando(string $action, array $data)
    {
        // Redirección de éxito
        $urlExito = '../indexExamenEntrada.php';
        // Redirección de error (vuelve al formulario)
        $urlRetorno = "./indexExamenAgregar.php";

        try {
            // Factory Method: Creación del DTO
            $dto = ExamenFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            // Atributo: `$command`
            $command = ExamenFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Atributo: `$mensajeError`
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación (El Command falló en validate())
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito (El Command se ejecutó correctamente)
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Examen Clínico de Entrada registrado correctamente.', 
                    $urlExito, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (El DAO falló)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al registrar el examen. Fallo en la base de datos.', 
                    $urlRetorno, 
                    'error',
                    false
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error',
                false
            );
        }
    }
  
}
?>