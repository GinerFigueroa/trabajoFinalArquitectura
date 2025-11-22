<?php
session_start();
include_once('./formEmisionBoletaFinal.php');

$obj = new formEmisionBoletaFinal();
$obj->formEmisionBoletaFinalShow();
?>