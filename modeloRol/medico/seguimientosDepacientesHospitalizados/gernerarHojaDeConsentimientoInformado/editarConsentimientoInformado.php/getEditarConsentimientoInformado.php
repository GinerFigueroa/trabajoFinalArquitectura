<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarConsentimientoInformado.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    $idConsentimiento = $_POST['idConsentimiento'] ?? null;
    $diagnostico = $_POST['diagnostico'] ?? '';
    $tratamiento = $_POST['tratamiento'] ?? '';

    if (empty($idConsentimiento)) {
        $objMensaje->mensajeSistemaShow('ID de consentimiento no válido.', '../indexConsentimientoInformado.php', 'systemOut', false);
        return;
    }

    $objControl = new controlEditarConsentimientoInformado();
    $objControl->editarConsentimiento($idConsentimiento, $diagnostico, $tratamiento);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexConsentimientoInformado.php', 'systemOut', false);
}
?>