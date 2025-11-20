<?php

session_start();

include_once('./formHistorialClinica.php');
$obj = new formHistorialClinica();
$obj->formHistorialClinicaShow();
?>