<?php
session_start();

include_once('./formAgregarExamenClinico.php');
$obj = new formAgregarExamenClinico();
$obj->formAgregarExamenClinicoShow();
?>