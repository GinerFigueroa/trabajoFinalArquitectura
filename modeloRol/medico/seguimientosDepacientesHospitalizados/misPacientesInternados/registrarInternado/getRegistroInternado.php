<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlRegistroInternado.php'); // Incluye el Mediator/Control

$objMensaje = new mensajeSistema();

if (isset($_POST['btnRegistrar'])) {
    // Recoger y sanitizar datos directamente en un array
    $data = [
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'idHabitacion' => $_POST['idHabitacion'] ?? null,
        'idMedico' => $_POST['idMedico'] ?? null,
        'fechaIngreso' => $_POST['fechaIngreso'] ?? null,
        'diagnostico' => $_POST['diagnostico'] ?? '',
        'observaciones' => $_POST['observaciones'] ?? '',
        // Se asume que el usuario de la sesión será el 'creadoPor' si es necesario.
        'creadoPor' => $_SESSION['id_usuario'] ?? 1 
    ];

    // PATRÓN MEDIATOR: Invocación del Mediador/Controlador
    $objControl = new controlRegistroInternado();
    $objControl->registrarInternado($data);
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "../indexGestionInternados.php", "error");
}
?>