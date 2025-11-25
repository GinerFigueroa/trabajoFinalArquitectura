<?php
session_start();
include_once('./formExamenAgregar.php'); 
$obj = new formExamenAgregar();
$obj->formExamenAgregarShow();
?>