<?php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarOrdenPreFactura.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    $idOrden = $_POST['idOrden'] ?? null;
    $concepto = $_POST['concepto'] ?? '';
    $monto = $_POST['monto'] ?? 0;

    if (empty($idOrden)) {
        $objMensaje->mensajeSistemaShow('ID de orden no válido.', '../indexOdenPrefactura.php', 'systemOut', false);
        return;
    }

    $objControl = new controlEditarOrdenPreFactura();
    $objControl->editarOrden($idOrden, $concepto, $monto);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexOdenPrefactura.php', 'systemOut', false);
}
?>