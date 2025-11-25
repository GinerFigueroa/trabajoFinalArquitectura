<?php
// Directorio: /controlador/historial/agregarOrdenMedico/controlAgregarExamenClinico.php

include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class OrdenExamenDTO {
    public $historiaClinicaId;
    public $idUsuarioMedico; 
    public $fecha;
    public $tipoExamen;
    public $indicaciones;
    public $estado;
    
    public function __construct(array $data) {
        $this->historiaClinicaId = (int)($data['historia_clinica_id'] ?? 0);
        $this->idUsuarioMedico   = (int)($data['id_medico'] ?? 0);
        $this->fecha             = trim($data['fecha'] ?? '');
        $this->tipoExamen        = trim($data['tipo_examen'] ?? '');
        $this->indicaciones      = trim($data['indicaciones'] ?? '');
        $this->estado            = trim($data['estado'] ?? 'Pendiente');
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {}

class OrdenExamenFactory {
    public static function crearDTO(array $data): OrdenExamenDTO {
        return new OrdenExamenDTO($data);
    }
    
    // Recibe el DAO para Inyección de Dependencia
    public static function crearComando(string $action, OrdenExamenDTO $dto, OrdenExamenDAO $dao): Comando { 
        switch ($action) {
            case 'agregar':
                return new AgregarOrdenExamenCommand($dto, $dao);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// COMMAND Concreto: Agregar Orden de Examen 📦
class AgregarOrdenExamenCommand implements Comando
{
    private $objDAO; 
    private $dto;
    private $validationMessage = null;

    // Recibe el DAO
    public function __construct(OrdenExamenDTO $dto, OrdenExamenDAO $dao)
    {
        $this->objDAO = $dao; // Asignación del DAO inyectado
        $this->dto = $dto;
    }
    
    public function execute(): bool
    {
        // 1. Validación de Datos Requeridos y Formato
        if ($this->dto->historiaClinicaId <= 0 || $this->dto->idUsuarioMedico <= 0 || empty($this->dto->tipoExamen)) {
            $this->validationMessage = "Faltan campos obligatorios (Paciente, Médico o Tipo de Examen).";
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dto->fecha) || strtotime($this->dto->fecha) > time()) {
            $this->validationMessage = "El formato de fecha no es válido o la fecha es futura.";
            return false;
        }

        // 2. Validación de Permisos y Existencia (Negocio)
        
        // Llama al método del DAO
        if (!$this->objDAO->esUsuarioMedico($this->dto->idUsuarioMedico)) {
            $this->validationMessage = "El usuario no tiene permisos de médico para crear órdenes.";
            return false;
        }

        // Llama al método del DAO. **LA LÍNEA QUE CAUSABA EL ERROR (85) está aquí**.
        if (!$this->objDAO->existeHistoriaClinica($this->dto->historiaClinicaId)) {
            $this->validationMessage = "La historia clínica seleccionada no existe.";
            return false;
        }

        // 3. Ejecución del Receptor (DAO)
        return $this->objDAO->registrarOrden(
            $this->dto->historiaClinicaId,
            $this->dto->idUsuarioMedico,
            $this->dto->fecha,
            $this->limpiarTexto($this->dto->tipoExamen),
            $this->limpiarTexto($this->dto->indicaciones),
            $this->dto->estado
        );
    }
    
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }

    private function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto));
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

class controlAgregarExamenClinico
{
    private $objMensaje;
    private $objDAO; 

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objDAO = new OrdenExamenDAO(); // Instanciamos el DAO aquí
    }

    public function ejecutarComando(string $action, array $data)
    {
        $urlRetornoExito = "../indexOrdenExamenClinico.php";
        $urlRetornoError = "./indexAgregarExamenClinico.php";

        try {
            $dto = OrdenExamenFactory::crearDTO($data);
            
            // Pasamos el DAO al Factory/Command
            $command = OrdenExamenFactory::crearComando($action, $dto, $this->objDAO); 

            $resultado = $command->execute();

            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de registro: " . $mensajeError,
                    $urlRetornoError,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Orden de examen creada correctamente.', 
                    $urlRetornoExito, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al crear la orden de examen. Fallo en la base de datos.', 
                    $urlRetornoError, 
                    'error'
                );
            }
        } catch (Exception $e) {
             // Estado 4: Error interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetornoError, 
                'error'
            );
        }
    }
}
?>