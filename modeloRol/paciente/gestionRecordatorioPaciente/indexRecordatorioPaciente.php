<?php
session_start();

// Validar sesión y permisos
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../../../securityModule/indexLoginSegurity.php");
    exit();
}

include_once('./formRecordatorioPaciente.php');

$objForm = new formRecordatorioPaciente();
$objForm->formRecordatorioPacienteShow();
?>