<?php
session_start();
include_once('./formEvolucionPacienteHospitalizado.php');
$obj = new formEvolucionPacienteHospitalizado();
$obj->formEvolucionPacienteHospitalizadoShow();
?>