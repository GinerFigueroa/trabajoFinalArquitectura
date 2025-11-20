<?php
// Archivo: getEditarCitas.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarCitas.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    
    // PATRÓN BUILDER (Construcción del DTO/Array de Datos)
    // Se asegura de que todos los campos esperados estén presentes y tipados.
    $datos = [
        'idCita' => $_POST['idCita'] ?? null,
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'idTratamiento' => $_POST['idTratamiento'] ?? null,
        'idMedico' => $_POST['idMedico'] ?? null,
        'fechaHora' => $_POST['fechaHora'] ?? null,
        'duracion' => $_POST['duracion'] ?? 30, // Default 30 min
        'estado' => $_POST['estado'] ?? 'Pendiente',
        'notas' => $_POST['notas'] ?? ''
    ];

    // DISPATCHER: Crea el Command y lo ejecuta
    $objControl = new controlEditarCitas();
    $objControl->editarCitaCommand($datos); // Ejecuta el Command
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexCita.php', 'systemOut', false);
}
?>