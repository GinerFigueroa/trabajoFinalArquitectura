<?php
// Archivo: indexFacturacionInternado.php

session_start();

include_once('./formFacturacionInternado.php');

$obj = new formFacturacionInternado();
$obj->formFacturacionInternadoShow();
?>