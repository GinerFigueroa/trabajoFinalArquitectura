<?php

session_start();
include_once('./formEvolucionPaciente.php');
$objForm = new formEvolucionPaciente();
$objForm->formEvolucionPacienteShow();
?>