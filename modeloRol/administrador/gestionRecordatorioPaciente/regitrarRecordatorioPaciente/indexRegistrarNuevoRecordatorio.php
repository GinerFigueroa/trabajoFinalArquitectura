<?php
session_start();

include_once('./formRegistrarNuevoRecordatorio.php');

$objForm = new formRegistrarNuevoRecordatorio();
$objForm->formRegistrarNuevoRecordatorioShow();
?>