<?php
// Directorio: /controlador/boleta/controlEmisionBoleta.php

include_once('../../../modelo/BoletaDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class BoletaDTO {
    // Atributo: $idBoleta
    public $idBoleta;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idBoleta = (int)($data['idBoleta'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class BoletaFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): BoletaDTO {
        // Crea y retorna el DTO
        return new BoletaDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, BoletaDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Crea y retorna el comando de anulación/eliminación
                return new AnularBoletaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// COMMAND Concreto: Anular Boleta 📦
class AnularBoletaCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (BoletaDAO)
    private $dto;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(BoletaDTO $dto)
    {
        $this->objDAO = new BoletaDAO();
        $this->dto = $dto;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // Validación de ID simple antes de ejecutar
        if ($this->dto->idBoleta <= 0) {
            $this->validationMessage = "El ID de la boleta no es válido o está ausente.";
            return false;
        }

        // Ejecución del receptor (DAO)
        // Se asume que `eliminarBoleta` también revierte el estado de la Orden.
        // Método: `eliminarBoleta`
        return $this->objDAO->eliminarBoleta($this->dto->idBoleta);
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
class controlEmisionBoleta
{
    // Eliminamos la dependencia directa a BoletaDAO (el Command la maneja)
    private $objMensaje;

    // Método: Constructor
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
        $urlRetorno = "./indexEmisionBoletaFinal.php";

        try {
            // Factory Method: Creación del DTO
            $dto = BoletaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            // Atributo: `$command`
            $command = BoletaFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Atributo: `$mensajeError`
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Boleta/Factura anulada y Orden de Pago restablecida a "Pendiente".', 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: Boleta no encontrada o fallo en DB)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al anular la boleta. Puede que ya no exista o haya un fallo en la DB.', 
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
    
    // Redireccionamos el método original `eliminarBoleta` al nuevo método `ejecutarComando`
    // para mantener la compatibilidad con el archivo getEmisionBoleta.php antes de su refactorización.
    public function eliminarBoleta($idBoleta)
    {
        $this->ejecutarComando('eliminar', ['idBoleta' => $idBoleta]);
    }
}
?>