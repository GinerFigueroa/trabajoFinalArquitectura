
<?php
// Archivo: controlDocumentos.php
include_once('../../../modelo/DocumentoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlDocumentos // PATRÓN: MEDIATOR / COMMAND
{
    private $objDocumentosDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDocumentosDAO = new DocumentosDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * PATRÓN: COMMAND (Ejecuta la acción de negocio: Eliminar).
     * @param int $idDocumento
     */
    public function eliminarDocumento($idDocumento)
    {
        if (empty($idDocumento) || !is_numeric($idDocumento) || $idDocumento <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de documento no válido para eliminar.", "./indexDocumento.php", "systemOut", false);
            return;
        }

        // Obtener la ruta para eliminar el archivo físico
        $documento = $this->objDocumentosDAO->obtenerDocumentoPorId($idDocumento);

        if (!$documento) {
             $this->objMensaje->mensajeSistemaShow("Documento no encontrado.", "./indexDocumento.php", "error", false);
             return;
        }

        // 2. Delegación al DAO (Ejecución del Command)
        $resultado = $this->objDocumentosDAO->eliminarDocumento($idDocumento);
        
        // 3. Manejo de la respuesta
        if ($resultado) {
            // Lógica para eliminar el archivo físico (asumiendo que la ruta es válida)
            if (file_exists($documento['ruta_archivo'])) {
                @unlink($documento['ruta_archivo']); // @ para suprimir errores si no se puede eliminar
            }

            $this->objMensaje->mensajeSistemaShow("Documento y archivo eliminados correctamente.", "./indexDumento.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar el documento. Puede que ya no exista o haya un error en la base de datos.", "./indexDumento.php", "error");
        }
    }
}