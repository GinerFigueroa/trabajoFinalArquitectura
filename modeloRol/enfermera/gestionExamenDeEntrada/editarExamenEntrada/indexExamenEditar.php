<?php

session_start();

include_once('./formExamenEditar.php');
$obj = new formExamenEditar();
$obj->formExamenEditarShow();
?>