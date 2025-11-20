<?php
include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');
include_once('./formHistorialAnemiaPDF.php');

class controlHistorialAnemiaPDF
{
    private $objHistorial;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objHistorial = new HistorialAnemiaPacienteDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formHistorialAnemiaPDF();
    }

    public function generarPDF()
    {
        $idAnamnesis = $_GET['id'] ?? null;
        
        if (empty($idAnamnesis) || !is_numeric($idAnamnesis)) {
            $this->objMensaje->mensajeSistemaShow(
                "ID de historial no proporcionado o no válido.", 
                "../indexHistorialAnemia.php", 
                "error"
            );
            return;
        }

        $historial = $this->objHistorial->obtenerHistorialPorId($idAnamnesis);

        if (!$historial) {
            $this->objMensaje->mensajeSistemaShow(
                "Historial de anemia no encontrado.", 
                "../indexHistorialAnemia.php", 
                "error"
            );
            return;
        }

        // Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($historial);
    }
}
?>