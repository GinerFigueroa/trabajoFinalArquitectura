<?php

session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarHistorialAnemia.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlAgregarHistorialAnemia();

if (isset($_POST['btnAgregar'])) {
    // MEDIATOR: Invoca el método coordinador con la acción y los datos POST.
    // Método: ejecutarComando
    $objControl->ejecutarComando('agregar', $_POST);

} else {
    // Si no es la acción esperada, redirigir
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialAnemia.php', 'error');
}
?>