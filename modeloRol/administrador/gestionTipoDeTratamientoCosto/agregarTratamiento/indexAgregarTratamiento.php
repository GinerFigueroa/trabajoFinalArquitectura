<?php

session_start();

include_once('./formAgregarTratamiento.php');
$obj = new formAgregarTratamiento();
$obj->formAgregarTratamientoShow();
?>