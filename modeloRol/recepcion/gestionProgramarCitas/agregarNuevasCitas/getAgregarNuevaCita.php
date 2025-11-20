<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarNuevaCita.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnAgregar'])) {
    // Builder: Recolección y Mapeo de datos (Preparación de la materia prima)
    $idPaciente = $_POST['idPaciente'] ?? null;
    $idTratamiento = $_POST['idTratamiento'] ?? null;
    $idMedico = $_POST['idMedico'] ?? null;
    $fechaHora = $_POST['fechaHora'] ?? null;
    $duracion = $_POST['duracion'] ?? 30;
    $estado = $_POST['estado'] ?? 'Pendiente';
    $notas = $_POST['notas'] ?? '';
    
    // El 'creadoPor' debe obtenerse de la sesión
    $creadoPor = $_SESSION['id_usuario'] ?? 1; // Asumiendo que el ID del usuario está en la sesión

    $objControl = new controlAgregarNuevaCita();
    // Invocación del Command a través del Control/Mediador
    $objControl->agregarNuevaCita($idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas, $creadoPor);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexCita.php', 'systemOut', false);
}
?>