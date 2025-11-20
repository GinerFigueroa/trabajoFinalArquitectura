<?php
session_start();
include_once('./formOrdenExamenClinico.php');
$obj = new formOrdenExamenClinico();
$obj->formOrdenExamenClinicoShow();
?>