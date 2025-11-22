<?php
// C:\...\editarHistorialPaciente\indexEditarHistorialPaciente.php
session_start();

include_once('./formEditarHistorialPaciente.php');
$obj = new formEditarHistorialPaciente();
$obj->formEditarHistorialPacienteShow();
?>