
<?php
// Archivo: controlEditarDocumento.php
include_once('../../../../modelo/DocumentoDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// PATRÓN: CHAIN OF RESPONSIBILITY (Interfaces y Handlers)
// Adaptado para Documentos
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

class DocRequiredFieldValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        if (empty($datos['idDocumento']) || empty($datos['idPaciente']) || empty($datos['tipo']) || empty($datos['nombre'])) {
            return 'Los campos ID, Paciente, Tipo y Nombre son obligatorios.';
        }
        if (!is_numeric($datos['idDocumento']) || $datos['idDocumento'] <= 0) {
            return 'ID de documento no válido.';
        }
        return $this->checkNext($datos);
    }
}

class DocTypeValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        $tiposPermitidos = ['Radiografía', 'Consentimiento', 'Historial', 'Otro'];
        if (!in_array($datos['tipo'], $tiposPermitidos)) {
            return 'Tipo de documento no válido.';
        }
        return $this->checkNext($datos);
    }
}

class DocEntityExistenceValidator extends DocumentoValidatorHandler {
    public function handle(array $datos): ?string {
        if (!$this->entidadDAO->pacienteExiste($datos['idPaciente'])) {
            return "El paciente seleccionado no es válido.";
        }
        return $this->checkNext($datos);
    }
}

// ==========================================================
// PATRÓN: FACTORY METHOD (Para construir la cadena de validación)
// ==========================================================
class DocumentoValidatorFactory {
    public static function createEditDocumentoValidatorChain(DocumentosDAO $dDao, EntidadesDAO $eDao): DocumentoValidatorHandler {
        $required = new DocRequiredFieldValidator($dDao, $eDao);
        $type = new DocTypeValidator($dDao, $eDao);
        $existence = new DocEntityExistenceValidator($dDao, $eDao);

        // Construir la Cadena de Responsabilidad
        $required->setNext($type)->setNext($existence);
        
        return $required;
    }
}


// ==========================================================
// PATRÓN: MEDIATOR / COMMAND (Lógica de Negocio)
// ==========================================================
class controlEditarDocumento
{
    private $objDocumentosDAO;
    private $objEntidadDAO;
    private $objMensaje;
    private $validatorChain;

    public function __construct()
    {
        $this->objDocumentosDAO = new DocumentosDAO();
        $this->objEntidadDAO = new EntidadesDAO();
        $this->objMensaje = new mensajeSistema();
        
        // Inicializa la Chain of Responsibility usando el Factory Method
        $this->validatorChain = DocumentoValidatorFactory::createEditDocumentoValidatorChain($this->objDocumentosDAO, $this->objEntidadDAO);
    }

    /**
     * Implementa el patrón COMMAND y MEDIATOR.
     */
    public function editarDocumentoCommand(array $datos) // COMMAND
    {
        $redirectUrl = './indexEditarDocumento.php?id=' . urlencode($datos['idDocumento']);

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
        $resultado = $this->objDocumentosDAO->editarDocumento(
            $datos['idDocumento'],
            $datos['idPaciente'],
            $datos['tipo'],
            $datos['nombre'],
            $datos['notas']
        );
        
        // 3. MEDIATOR: Manejo de la respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Documento editado correctamente (Metadatos).', '../indexDumento.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar el documento. No se detectaron cambios o hubo un error en BD.', $redirectUrl, 'error');
        }
    }
}