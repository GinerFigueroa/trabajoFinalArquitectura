<?php
include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');
include_once('./formOrdenExamenClinicoPDF.php');

class controlExamenClinicoPDF
{
    private $objOrden;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objOrden = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formOrdenExamenClinicoPDF();
    }

    public function generarPDF()
    {
        $idOrden = $_GET['id'] ?? null;
        
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow(
                "ID de Orden de Examen no proporcionado o no válido.", 
                "../indexOrdenExamenClinico.php", 
                "error"
            );
            return;
        }

        $orden = $this->objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            $this->objMensaje->mensajeSistemaShow(
                "Orden de Examen no encontrada.", 
                "../indexOrdenExamenClinico.php", 
                "error"
            );
            return;
        }

        // Validar datos mínimos
        if (empty($orden['nombre_paciente']) || empty($orden['nombre_medico']) || empty($orden['tipo_examen'])) {
            $this->objMensaje->mensajeSistemaShow(
                "La orden de examen no tiene todos los datos necesarios para generar el PDF.", 
                "../indexOrdenExamenClinico.php", 
                "error"
            );
            return;
        }

        // Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($orden);
    }
}