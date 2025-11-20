<?php

session_start();

include_once('./formPacienteAgregar.php');
$obj = new formPacienteAgregar();
$obj->formPacienteAgregarShow();
?>