<?php
// FILE: getEditarOrdenPreFactura.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarOrdenPreFactura.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    // 1. Recolección de datos
    $data = [
        'idOrden' => $_POST['idOrden'] ?? null,
        'concepto' => $_POST['concepto'] ?? '',
        'monto' => $_POST['monto'] ?? 0,
        // Se pueden añadir datos de auditoría si fuera necesario (ejecutadoPor)
    ];

    if (empty($data['idOrden'])) {
        $objMensaje->mensajeSistemaShow('ID de orden no válido.', '../indexOdenPrefactura.php', 'systemOut', false);
        return;
    }

    // 2. El Invocador delega la tarea al Mediator
    $objControl = new controlEditarOrdenPreFactura(); // PATRÓN: MEDIATOR
    // MÉTODO
    $objControl->ejecutarAccion($data); 
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexOdenPrefactura.php', 'systemOut', false);
}
?>