<?php

session_start();
// Atributo: $obj (Instancia de la Vista)
include_once('./formOrdenExamenClinico.php');
$obj = new formOrdenExamenClinico();
// Método: formOrdenExamenClinicoShow (Método del Template)
$obj->formOrdenExamenClinicoShow();
?>