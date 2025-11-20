<?php
session_start();
include_once('./formTotalPaciente.php');
$obj = new formTotalPaciente();
$obj->formTotalPacienteShow();
?>