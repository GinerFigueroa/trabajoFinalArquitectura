<?php

include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarOrdenPreFactura
{
    private $objOrden;
    private $objMensaje;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarOrden($idOrden, $concepto, $monto)
    {
        $urlRetorno = './indexEditarOrdenPreFactura.php?id=' . $idOrden;

        // 1. Validación de campos obligatorios
        if (empty($idOrden) || empty($concepto) || !is_numeric($monto) || $monto <= 0) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios o el monto no es válido.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Ejecutar la edición (el modelo solo permite editar si el estado es 'Pendiente')
        $resultado = $this->objOrden->editarOrden($idOrden, $concepto, $monto);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura N° ' . $idOrden . ' actualizada correctamente.', '../indexOdenPrefactura.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar la orden. La orden no se encuentra en estado "Pendiente" o no se realizaron cambios.', $urlRetorno, 'error');
        }
    }
}
?>