<?php

session_start();

include_once('./formEditarEvolucionPaciente.php');

$objForm = new formEditarEvolucionPaciente();
$objForm->formEditarEvolucionPacienteShow();
?>