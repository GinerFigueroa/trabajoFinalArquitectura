<?php

session_start();

// Atributo: $obj (Instancia de la Vista)
include_once('./formEditarExamenClinico.php');
$obj = new formEditarExamenClinico();
// Método: formEditarExamenClinicoShow (Método del Template)
$obj->formEditarExamenClinicoShow();
?>