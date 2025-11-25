<?php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlConsentimientoInformado.php');

$objMensaje = new mensajeSistema();
// Atributo: `$objControl` (El Mediator)
$objControl = new controlConsentimientoInformado();

// Recoger y limpiar datos
$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$rutaRetorno = "./indexConsentimientoInformado.php";

if ($action == 'eliminar' && $id > 0) {
    $data = [
        // Atributo: `id` (PK del consentimiento)
        'id' => $id
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('eliminar', $data);

} else {
    // Si no es la acción esperada, redirigir
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", $rutaRetorno, "systemOut", false);
}
?>