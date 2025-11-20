<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarHistorialAnemia.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    $objControl = new controlEditarHistorialAnemia();
    $objControl->editarHistorial($_POST);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialAnemia.php', 'error');
}
?>