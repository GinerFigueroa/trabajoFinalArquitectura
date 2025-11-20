<?php
session_start();
include_once('./formEvolucionPaciente.php');

$obj = new formEvolucionPaciente();
$obj->formEvolucionPacienteShow();
?>