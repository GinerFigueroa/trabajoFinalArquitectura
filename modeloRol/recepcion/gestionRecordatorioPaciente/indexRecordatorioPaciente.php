<?php
session_start();

include_once('./formRecordatorioPaciente.php');

$objForm = new formRecordatorioPaciente();
$objForm->formRecordatorioPacienteShow();
?>