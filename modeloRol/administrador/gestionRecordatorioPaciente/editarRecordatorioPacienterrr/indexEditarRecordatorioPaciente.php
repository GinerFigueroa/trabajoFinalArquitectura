<?php
session_start();
include_once('./formEditarRecordatorioPaciente.php');

$objForm = new formEditarRecordatorioPaciente();
$objForm->formEditarRecordatorioPacienteShow();
?>