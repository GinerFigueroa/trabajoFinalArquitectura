

<?php
// Directorio: /controlador/gestionDetalleCitaPaciente/controlAgregarDetalleCita.php

session_start();
include_once('../../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

/**
 * DetalleCitaDTO (Data Transfer Object)
 * Utilizado para transferir y sanitizar los datos del formulario.
 */
class DetalleCitaDTO {
    public $idReceta;
    public $medicamento;
    public $dosis;
    public $frecuencia;
    public $duracion;
    public $notas;
    public $idUsuarioMedico;
    
    public function __construct(array $data) {
        // Sanitización de datos de entrada
        $this->idReceta = (int)($data['idReceta'] ?? 0);
        $this->medicamento = trim(htmlspecialchars($data['medicamento'] ?? ''));
        $this->dosis = trim(htmlspecialchars($data['dosis'] ?? ''));
        $this->frecuencia = trim(htmlspecialchars($data['frecuencia'] ?? ''));
        $this->duracion = $data['duracion'] ? trim(htmlspecialchars($data['duracion'])) : null;
        $this->notas = $data['notas'] ? trim(htmlspecialchars($data['notas'])) : null;
        $this->idUsuarioMedico = (int)($data['idUsuarioMedico'] ?? 0);
    }
}

/**
 * Interfaz ComandoDetalle
 * Interfaz base para todos los comandos (Patrón Command).
 */
interface ComandoDetalle {
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * DetalleCitaFactory (Patrón Factory Method) 🏭
 * Encargada de crear las instancias de DTO y Comandos.
 */
class DetalleCitaFactory {
    public static function crearDTO(array $data): DetalleCitaDTO {
        return new DetalleCitaDTO($data);
    }
    
    public static function crearComando(string $action, DetalleCitaDTO $dto): ComandoDetalle {
        switch ($action) {
            case 'agregar':
                return new AgregarDetalleCitaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Detalle de Receta.");
        }
    }
}

/**
 * AgregarDetalleCitaCommand (Command Concreto) 📦
 * Encapsula toda la lógica de validación y registro del detalle de la receta.
 * Implementa el Patrón State internamente para reportar el resultado.
 */
class AgregarDetalleCitaCommand implements ComandoDetalle
{
    private $objDAO; // Receptor (RecetaDetalleDAO)
    private $dto;
    // Atributos de Estado de la operación
    private $validationMessage = null; 
    private $newId = null;

    public function __construct(DetalleCitaDTO $dto)
    {
        $this->objDAO = new RecetaDetalleDAO();
        $this->dto = $dto;
    }
    
    /**
     * Ejecuta la lógica del comando: validación de datos, validación de negocio y registro.
     */
    public function execute(): bool
    {
        // 1. Validaciones de Datos (Integridad y Obligatoriedad)
        if ($this->dto->idReceta <= 0) {
            $this->validationMessage = "Debe seleccionar una receta médica válida.";
            return false;
        }
        if (empty($this->dto->medicamento) || strlen($this->dto->medicamento) < 2) {
            $this->validationMessage = "El nombre del medicamento es obligatorio y debe tener al menos 2 caracteres.";
            return false;
        }
        if (empty($this->dto->dosis) || strlen($this->dto->dosis) < 1) {
            $this->validationMessage = "La dosis es obligatoria.";
            return false;
        }
        if (empty($this->dto->frecuencia)) {
            $this->validationMessage = "La frecuencia es obligatoria.";
            return false;
        }
        
        // 2. Validación de Negocio (Seguridad)
        
        // Verificar que la receta existe
        if (!$this->objDAO->existeReceta($this->dto->idReceta)) {
            $this->validationMessage = "La receta seleccionada no existe.";
            return false;
        }
        
        // Verificar que el médico es el dueño de la receta (Seguridad clave)
        $idUsuarioReceta = $this->objDAO->obtenerIdUsuarioPorIdReceta($this->dto->idReceta);
        
        if ($idUsuarioReceta != $this->dto->idUsuarioMedico) {
            $this->validationMessage = "No puede agregar detalles a recetas creadas por otros médicos.";
            return false;
        }

        // 3. Ejecución del Receptor (DAO)
        $resultadoId = $this->objDAO->registrarDetalle(
            $this->dto->idReceta, 
            $this->dto->medicamento, 
            $this->dto->dosis, 
            $this->dto->frecuencia, 
            $this->dto->duracion, 
            $this->dto->notas
        );

        if ($resultadoId) {
            $this->newId = $resultadoId;
            return true;
        }
        
        $this->validationMessage = "Error en la base de datos al registrar el detalle.";
        return false;
    }

    // Métodos para leer el Estado de la operación (Patrón State)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    public function getNewId(): ?int
    {
        return $this->newId;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlAgregarDetalleCita (Patrón Mediator) 🤝
 * Coordina la creación del comando, su ejecución y el manejo de los mensajes de salida.
 */
class controlAgregarDetalleCita
{
    private $objMensaje;
    private $objDetalleDAO;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objDetalleDAO = new RecetaDetalleDAO();
    }

    /**
     * Punto de coordinación central.
     * Patrón: STATE 🚦 (Manejo de estados basado en la salida del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "../indexDetalleCita.php";
        $urlRetornoFallo = "./indexAgregarDetalleCita.php";

        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = DetalleCitaFactory::crearDTO($data);
            $command = DetalleCitaFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();
            $newId = $command->getNewId();

            if ($mensajeError) {
                // Estado 1: Error de validación o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación/permisos: " . $mensajeError,
                    $urlRetornoFallo,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Detalle de receta agregado correctamente. ID: ' . $newId, 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al agregar el detalle de receta. Verifique la base de datos.', 
                    $urlRetornoFallo, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de sistema (Ej: Factory no encuentra la acción)
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error'
            );
        }
    }
    
    // ----------------------------------------------------------------------
    // MÉTODOS AUXILIARES (Para la vista, si se hubiera refactorizado)
    // ----------------------------------------------------------------------

    /**
     * Valida si el usuario logueado es médico
     */
    private function validarUsuarioMedico()
    {
        return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2 && isset($_SESSION['id_usuario']);
    }

    /**
     * Obtiene solo las recetas creadas por el médico logueado.
     * (Este método no es llamado por la Vista original, pero es la forma correcta de obtener datos)
     */
    public function obtenerRecetasDelMedicoLogueado()
    {
        if (!$this->validarUsuarioMedico()) {
            return [];
        }
        
        $idUsuarioMedico = $_SESSION['id_usuario'];
        $todasRecetas = $this->objDetalleDAO->obtenerRecetasMedicas(); 
        $recetasDelMedico = [];

        foreach ($todasRecetas as $receta) {
            $idUsuarioReceta = $this->objDetalleDAO->obtenerIdUsuarioPorIdReceta($receta['id_receta']);
            if ($idUsuarioReceta == $idUsuarioMedico) {
                $recetasDelMedico[] = $receta;
            }
        }

        return $recetasDelMedico;
    }
}
?>