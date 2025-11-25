<?php
// Directorio: /controlador/consentimiento/editarConsentimientoInformado/getEditarConsentimientoInformado.php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarConsentimientoInformado.php');

$objMensaje = new mensajeSistema();
// Atributo: `$objControl` (El Mediator)
$objControl = new controlEditarConsentimientoInformado();

if (isset($_POST['btnEditar'])) {
    // Recoger datos
    $data = [
        // Atributo: `idConsentimiento`
        'idConsentimiento' => $_POST['idConsentimiento'] ?? null,
        // Atributo: `diagnostico`
        'diagnostico' => $_POST['diagnostico'] ?? '',
        // Atributo: `tratamiento`
        'tratamiento' => $_POST['tratamiento'] ?? '',
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('editar', $data);

} else {
    // Si no es la acción esperada, redirigir
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexConsentimientoInformado.php', 'systemOut', false);
}
?>