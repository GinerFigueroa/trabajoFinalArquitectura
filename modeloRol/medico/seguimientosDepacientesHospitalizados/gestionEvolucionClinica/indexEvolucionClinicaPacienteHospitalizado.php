<?php

session_start();
// Atributo: `formEvolucionPacienteHospitalizado` (Clase de la Vista)
include_once('./formEvolucionPacienteHospitalizado.php'); 
$obj = new formEvolucionPacienteHospitalizado();
// Método: `formEvolucionPacienteHospitalizadoShow`
$obj->formEvolucionPacienteHospitalizadoShow();
?>