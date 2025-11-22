<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecuperarPasword.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnSolicitarCodigo'])) {
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $objMensaje->mensajeSistemaShow(
            "El email es obligatorio", 
            "./indexRecuperarPasword.php", 
            "error"
        );
        return;
    }

    $objControl = new controlRecuperarPasword();
    $objControl->procesarSolicitud($email);
} else {
    $objMensaje->mensajeSistemaShow(
        "Acceso no permitido", 
        "./indexRecuperarPasword.php", 
        "error"
    );
}
?>