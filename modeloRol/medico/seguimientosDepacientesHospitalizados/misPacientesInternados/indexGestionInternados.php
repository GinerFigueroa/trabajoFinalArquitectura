<?php
session_start();
include_once('./formGestionInternados.php');
$obj = new formGestionInternados();
$obj->formGestionInternadosShow();
?>