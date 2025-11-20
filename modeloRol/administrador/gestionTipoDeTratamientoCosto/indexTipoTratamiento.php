<?php

session_start();

include_once('./formTratamiento.php');
$obj = new formTratamiento();
$obj->formTratamientoShow();
?>