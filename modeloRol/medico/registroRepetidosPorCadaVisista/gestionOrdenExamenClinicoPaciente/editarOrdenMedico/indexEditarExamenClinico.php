<?php

session_start();

include_once('./formEditarExamenClinico.php');
$obj = new formEditarExamenClinico();
$obj->formEditarExamenClinicoShow();
?>