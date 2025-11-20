<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlRegistroInternado.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnRegistrar'])) {
    // Recoger y sanitizar datos
    $idPaciente = $_POST['idPaciente'] ?? null;
    $idHabitacion = $_POST['idHabitacion'] ?? null;
    $idMedico = $_POST['idMedico'] ?? null;
    $fechaIngreso = $_POST['fechaIngreso'] ?? null;
    $diagnostico = $_POST['diagnostico'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    $objControl = new controlRegistroInternado();
    $objControl->registrarInternado($idPaciente, $idHabitacion, $idMedico, $fechaIngreso, $diagnostico, $observaciones);
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "../indexGestionInternados.php", "error");
}
?>