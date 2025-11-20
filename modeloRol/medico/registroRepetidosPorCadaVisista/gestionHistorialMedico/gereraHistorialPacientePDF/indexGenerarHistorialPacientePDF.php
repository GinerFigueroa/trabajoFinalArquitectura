<?php
session_start();
include_once('./formGenerarHistorialPacientePDF.php');
$obj = new formGenerarHistorialPacientePDF();
$obj->formGenerarHistorialPacientePDFShow();
?>