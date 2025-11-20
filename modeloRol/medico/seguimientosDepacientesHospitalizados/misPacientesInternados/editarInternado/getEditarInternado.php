<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarInternado.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    // Recoger y sanitizar datos
    $idInternado = $_POST['idInternado'] ?? null;
    $idHabitacionAnterior = $_POST['idHabitacionAnterior'] ?? null;
    $idHabitacion = $_POST['idHabitacion'] ?? null;
    $idMedico = $_POST['idMedico'] ?? null;
    $fechaAlta = $_POST['fechaAlta'] ?? null;
    $diagnostico = $_POST['diagnostico'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $estado = $_POST['estado'] ?? '';

    $objControl = new controlEditarInternado();
    $objControl->editarInternado(
        $idInternado, 
        $idHabitacion, 
        $idMedico, 
        $fechaAlta, 
        $diagnostico, 
        $observaciones, 
        $estado, 
        $idHabitacionAnterior
    );
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "../indexGestionInternados.php", "error");
}
?>