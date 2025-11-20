<?php
// Archivo: controlEmisionBoleta.php (Controlador principal para acciones de lista)

include_once('../../../modelo/BoletaDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlEmisionBoleta
{
    private $objBoletaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objBoletaDAO = new BoletaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Elimina un registro de boleta y revierte la orden a Pendiente.
     * @param int $idBoleta
     */
    public function eliminarBoleta($idBoleta)
    {
        $urlRedireccion = "./indexEmisionBoletaFinal.php";
        
        if ($this->objBoletaDAO->eliminarBoleta($idBoleta)) {
            $this->objMensaje->mensajeSistemaShow("Boleta/Factura eliminada y Orden de Pago restablecida a 'Pendiente'.", $urlRedireccion, "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la boleta/factura. Consulte logs.", $urlRedireccion, "error");
        }
    }
}
?>