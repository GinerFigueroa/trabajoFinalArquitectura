<?php
// Asegúrate de que las rutas de los includes sean correctas
include_once('../../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('./formConcentimientoInformadoPDF.php'); // La vista que acabas de modificar

class ControlConsentimientoInformadoPDF
{
    private $objDAO;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        // Asumiendo que ConsentimientoInformadoDAO incluye el DAO o es el DAO en sí
        $this->objDAO = new ConsentimientoInformadoDAO(); 
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formConcentimientoInformadoPDF();
    }

    public function generarPDF()
    {
        // Obtener el ID del parámetro GET, igual que en el controlOrdenPDF
        $idConsentimiento = $_GET['id'] ?? null; 
        
        // 1. Validar ID
        if (empty($idConsentimiento) || !is_numeric($idConsentimiento)) {
            $this->objMensaje->mensajeSistemaShow("ID de Consentimiento no proporcionado o no válido.", "../indexConsentimientoInformado.php", "systemOut", false);
            return;
        }

        // 2. Obtener datos del DAO
        // ******************************************************************
        // CRÍTICO: Asegúrate de que el DAO tenga este método: obtenerConsentimientoPorId
        // que hace los JOINs necesarios para obtener: paciente, médico, HC, etc.
        // ******************************************************************
        $consentimiento = $this->objDAO->obtenerConsentimientoPorId($idConsentimiento);

        if (!$consentimiento) {
            $this->objMensaje->mensajeSistemaShow("Consentimiento Informado no encontrado.", "../indexConsentimientoInformado.php", "systemOut", false);
            return;
        }

        // 3. Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($consentimiento);
    }
}
