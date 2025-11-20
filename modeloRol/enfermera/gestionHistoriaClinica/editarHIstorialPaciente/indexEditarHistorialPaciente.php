<?php
// Fichero: gestionHistoriaClinica/editarHistorialPaciente/indexEditarHistorialPaciente.php
session_start();

// Validar que se haya pasado un ID de Historia Clínica
$historiaClinicaId = $_GET['id'] ?? null;

if (empty($historiaClinicaId)) {
    // Redirigir si no hay ID (puedes usar mensajeSistema si prefieres)
    header("Location: ../indexHistoriaClinica.php");
    exit();
}

include_once('./formEditarHistorialPaciente.php');
$objForm = new formEditarHistorialPaciente();

// El formulario llamará al controlador para obtener los datos
$objForm->formEditarHistorialPacienteShow($historiaClinicaId);
?>