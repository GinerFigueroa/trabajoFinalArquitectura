<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlCitas.php');

$objMensaje = new mensajeSistema();
$objControl = new controlCitas();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $idCita = $_GET['id'];
    $action = $_GET['action'];
    $idUsuario = $_SESSION['id_usuario'] ?? null;
    
    if (!is_numeric($idCita) || !$idUsuario) {
        $objMensaje->mensajeSistemaShow("Datos inválidos.", "./indexCitas.php", "error");
        exit;
    }

    switch ($action) {
        case 'cancelar':
            $objControl->cancelarCita($idCita, $idUsuario);
            break;
        default:
            $objMensaje->mensajeSistemaShow("Acción no válida.", "./indexCitas.php", "error");
            break;
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "./indexCitas.php", "error");
}
?>