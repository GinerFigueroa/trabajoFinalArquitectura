<?php

session_start();

include_once('./fromHistorialMedico.php');

$obj = new formHistorialClinica();
$obj->formHistorialClinicaShow();
?>


