<?php
session_start();
include_once('./formGestionUsuario.php');
$obj = new formGestionUsuario();
$obj->formGestionUsuarioShow();
?>