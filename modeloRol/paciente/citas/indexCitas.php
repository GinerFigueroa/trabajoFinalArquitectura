<?php
session_start();
include_once('./formConsultarCitas.php');
$obj = new formConsultarCitas();
$obj->formConsultarCitasShow();
?>