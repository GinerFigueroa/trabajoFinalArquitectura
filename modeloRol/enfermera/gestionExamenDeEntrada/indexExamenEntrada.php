<?php
session_start();
include_once('./formExamenEntrada.php');
$obj = new formExamenEntrada();
$obj->formExamenEntradaShow();
?>