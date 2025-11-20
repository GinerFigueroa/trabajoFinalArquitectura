<?php

session_start();


include_once('./formHistorialAnemia.php');
$obj = new formHistorialAnemia();
$obj->formHistorialAnemiaShow();
?>