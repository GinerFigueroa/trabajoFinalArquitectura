<?php
session_start();
include_once('./formRecuperarPasword.php');

$obj = new formRecuperarPasword();
$obj->formRecuperarPaswordShow();
?>