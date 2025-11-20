<?php

include_once('../../../modelo/ordenPagoDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlOrdenPrefactura
{
    private $objOrden;
    private $objMensaje;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarOrden($idOrden)
    {
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        $resultado = $this->objOrden->eliminarOrden($idOrden);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Orden de prefactura eliminada correctamente. (Solo se eliminan órdenes Pendientes).", "./indexOdenPrefactura.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la orden de prefactura o la orden ya no está Pendiente.", "./indexOdenPrefactura.php", "error");
        }
    }
}
?>