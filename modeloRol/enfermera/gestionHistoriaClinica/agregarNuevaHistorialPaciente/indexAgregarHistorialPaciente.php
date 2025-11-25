<?php

session_start();

include_once('./formAgregarHistorialPaciente.php');
$obj = new formAgregarHistorialPaciente();
$obj->formAgregarHistorialPacienteShow();
?>