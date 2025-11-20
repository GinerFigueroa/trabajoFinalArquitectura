<?php
// Archivo: controlFacturacionInternado.php

include_once('../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlFacturacionInternado
{
    private $objFacturaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objFacturaDAO = new FacturacionInternadoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Elimina un registro de factura de internado.
     * @param int $idFactura
     */
    public function eliminarFacturaInternado($idFactura)
    {
        $urlRedireccion = "./indexFacturacionInternado.php";
        
        if ($this->objFacturaDAO->eliminarFacturaInternado($idFactura)) {
            $this->objMensaje->mensajeSistemaShow("Factura de Internado eliminada correctamente.", $urlRedireccion, "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la factura de Internado de la base de datos.", $urlRedireccion, "error");
        }
    }
}
?>