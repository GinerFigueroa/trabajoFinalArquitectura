<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../../../securityModule/indexLoginSegurity.php");
    exit();
}

include_once('./formRegistrarNuevoRecordatorio.php');

$objForm = new formRegistrarNuevoRecordatorio();
$objForm->formRegistrarNuevoRecordatorioShow();
?>