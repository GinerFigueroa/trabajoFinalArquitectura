<?php

include_once('../../../modelo/DocumentoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * DocumentoDTO (Data Transfer Object)
 * Utilizado para transferir y sanitizar los datos necesarios para una operación.
 */
class DocumentoDTO {
    public $idDocumento;
    public $idUsuario; // ID del usuario que realiza la operación
    
    public function __construct(array $data) {
        // Sanitización de datos de entrada
        $this->idDocumento = (int)($data['idDocumento'] ?? 0);
        $this->idUsuario = (int)($data['idUsuario'] ?? 0);
    }
}

/**
 * Interfaz ComandoDocumento
 * Interfaz base para todos los comandos (Patrón Command).
 */
interface ComandoDocumento {
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * DocumentoFactory (Patrón Factory Method) 🏭
 * Encargada de crear las instancias de DTO y Comandos.
 */
class DocumentoFactory {
    public static function crearDTO(array $data): DocumentoDTO {
        return new DocumentoDTO($data);
    }
    
    public static function crearComando(string $action, DocumentoDTO $dto): ComandoDocumento {
        switch ($action) {
            case 'eliminar':
                return new EliminarDocumentoCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Documentos.");
        }
    }
}

/**
 * EliminarDocumentoCommand (Command Concreto) 📦
 * Encapsula la lógica de validación, eliminación de DB y eliminación del archivo físico.
 * Implementa el Patrón State para reportar el resultado.
 */
class EliminarDocumentoCommand implements ComandoDocumento
{
    private $objDAO; // Receptor (DocumentoDAO)
    private $dto;
    private $validationMessage = null; 
    private $rutaArchivo = null;

    public function __construct(DocumentoDTO $dto)
    {
        $this->objDAO = new DocumentosDAO();
        $this->dto = $dto;
    }
    
    /**
     * Ejecuta la lógica del comando.
     */
    public function execute(): bool
    {
        // 1. Validaciones de Datos
        if ($this->dto->idDocumento <= 0) {
            $this->validationMessage = "ID de documento no válido.";
            return false;
        }

        // 2. Validación de Negocio y Seguridad (¿Puede este usuario eliminar este documento?)
        $documento = $this->objDAO->obtenerDocumentoPorId($this->dto->idDocumento);
        
        if (!$documento) {
            $this->validationMessage = "El documento no existe o ya fue eliminado.";
            return false;
        }
        
        // **ASUMO SEGURIDAD**: Solo el usuario que subió el documento puede eliminarlo (O un Admin).
        // Si el rol es 1 (Admin), puede eliminar. Si no, debe coincidir el ID de usuario.
        if (($_SESSION['rol_id'] ?? 0) != 1 && $documento['id_usuario_subio'] != $this->dto->idUsuario) {
             $this->validationMessage = "Permisos insuficientes. Solo el usuario que subió el documento o un administrador pueden eliminarlo.";
             return false;
        }
        
        // Guardar la ruta del archivo antes de eliminar el registro en DB
        $this->rutaArchivo = $documento['ruta_archivo'];

        // 3. Ejecución del Receptor (DAO)
        $resultado = $this->objDAO->eliminarDocumento($this->dto->idDocumento);

        if ($resultado) {
            // 4. Lógica Adicional (Eliminar el archivo físico)
            if ($this->rutaArchivo && file_exists($this->rutaArchivo)) {
                // @ para suprimir errores si el archivo no existe o no se puede eliminar
                @unlink($this->rutaArchivo); 
            }
            return true;
        }
        
        $this->validationMessage = "Error en la base de datos al eliminar el registro del documento.";
        return false;
    }

    // Métodos para leer el Estado de la operación (Patrón State)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlDocumentos (Patrón Mediator) 🤝
 * Coordina la creación del comando, su ejecución y el manejo de los mensajes de salida.
 */
class controlDocumentos
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Punto de coordinación central.
     * Patrón: STATE 🚦 (Manejo de estados basado en la salida del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "./indexDocumento.php";
        
        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = DocumentoFactory::crearDTO($data);
            $command = DocumentoFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Documento y archivo eliminados correctamente.', 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar el documento. La operación falló en la base de datos.', 
                    $urlRetorno, 
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
}
?>