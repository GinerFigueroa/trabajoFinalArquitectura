<?php
// Archivo: controlAgregarDocumento.php
include_once('../../../../modelo/DocumentoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Interfaces y Handlers)
// ==========================================================
abstract class DocumentoValidatorHandler {
    protected $nextHandler = null;
    protected $documentosDAO;
    protected $entidadDAO;

    public function __construct(DocumentosDAO $dDao, EntidadesDAO $eDao) {
        $this->documentosDAO = $dDao;
        $this->entidadDAO = $eDao;
    }

    public function setNext(DocumentoValidatorHandler $handler): DocumentoValidatorHandler {
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

class AddRequiredFieldValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        if (empty($datos['idPaciente']) || empty($datos['tipo']) || 
            empty($datos['nombre']) || empty($datos['archivo']['name'])) {
            return 'Todos los campos marcados con (*) son obligatorios, incluyendo el archivo.';
        }
        return $this->checkNext($datos);
    }
}

class AddFileTypeValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        $archivo = $datos['archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        
        if (!in_array($extension, $extensionesPermitidas)) {
            return 'Tipo de archivo no permitido. Solo se aceptan PDF, JPG y PNG.';
        }
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
             return 'Error en la subida del archivo: Código ' . $archivo['error'];
        }
        if ($archivo['size'] > 5242880) {
             return 'El archivo excede el tamaño máximo permitido (5MB).';
        }
        
        return $this->checkNext($datos);
    }
}

class AddDocTypeAndExistenceValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        $tiposPermitidos = ['Radiografía', 'Consentimiento', 'Historial', 'Otro'];
        if (!in_array($datos['tipo'], $tiposPermitidos)) {
            return 'Tipo de documento no válido.';
        }
        if (!$this->entidadDAO->pacienteExiste($datos['idPaciente'])) {
            return "El paciente seleccionado no es válido.";
        }
        return $this->checkNext($datos);
    }
}

// ==========================================================
// PATRÓN: FACTORY METHOD
// ==========================================================
class AddDocumentoValidatorFactory {
    public static function createAddDocumentoValidatorChain(DocumentosDAO $dDao, EntidadesDAO $eDao): DocumentoValidatorHandler {
        $required = new AddRequiredFieldValidator($dDao, $eDao);
        $fileType = new AddFileTypeValidator($dDao, $eDao);
        $existence = new AddDocTypeAndExistenceValidator($dDao, $eDao);

        $required->setNext($fileType)->setNext($existence);
        return $required;
    }
}

// ==========================================================
// PATRÓN: MEDIATOR / COMMAND (Lógica de Negocio)
// ==========================================================
class controlAgregarDocumento
{
    private $objDocumentosDAO;
    private $objEntidadDAO;
    private $objMensaje;
    private $validatorChain;
    
    // Directorios ajustados para resolver problema de rutas
    private $uploadDir = '../../../../archivos_documentos/'; // Ruta física
    private $uploadDirWeb = 'TRABAJOFINALARQUITECTURA/archivos_documentos/';

    public function __construct()
    {
        $this->objDocumentosDAO = new DocumentosDAO();
        $this->objEntidadDAO = new EntidadesDAO();
        $this->objMensaje = new mensajeSistema();
        
        $this->validatorChain = AddDocumentoValidatorFactory::createAddDocumentoValidatorChain(
            $this->objDocumentosDAO, 
            $this->objEntidadDAO
        );

        // Crear directorio si no existe
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Implementa el patrón COMMAND y MEDIATOR.
     */
    public function registrarDocumentoCommand(array $datos)
    {
        $redirectUrl = './indexAgregarDocumento.php';

        // 1. Validación mediante cadena de responsabilidad
        $validationError = $this->validatorChain->handle($datos);
        if ($validationError) {
            $this->objMensaje->mensajeSistemaShow($validationError, $redirectUrl, 'systemOut', false);
            return;
        }

        // 2. Subida de archivo con rutas corregidas
        $archivo = $datos['archivo'];
        $nombreUnico = uniqid('doc_') . '_' . time() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        // Rutas corregidas (física y web)
        $rutaFisica = $this->uploadDir . $nombreUnico;
        $rutaWeb = $this->uploadDirWeb . $nombreUnico;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
            $this->objMensaje->mensajeSistemaShow('Error crítico al mover el archivo subido. Reintente.', $redirectUrl, 'error', false);
            return;
        }

        // 3. Registro en BD con ruta web
        $idUsuarioActual = $_SESSION['id_usuario'] ?? 1;

        $resultado = $this->objDocumentosDAO->registrarDocumento(
            $datos['idPaciente'],
            $datos['tipo'],
            $datos['nombre'],
            $rutaWeb, // Almacena ruta web en lugar de física
            $datos['notas'],
            $idUsuarioActual
        );
        
        // 4. Manejo de respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Documento subido y registrado correctamente.', 
                '../indexDocumento.php', 
                'success'
            );
        } else {
            @unlink($rutaFisica);
            $this->objMensaje->mensajeSistemaShow(
                'Error en el registro del documento en la base de datos.', 
                $redirectUrl, 
                'error'
            );
        }
    }
}
?>
