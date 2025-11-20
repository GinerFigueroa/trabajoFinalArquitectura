<?php
session_start();
include_once('./formTotalCitas.php');
$obj = new formTotalCitas();
$obj->formTotalCitasShow();
?>