<?php
// Archivo: indexEditarFacturacionInternado.php

session_start();

include_once('./formEditarFacturacionInternado.php');
    
$obj = new formEditarFacturacionInternado();
$obj->formEditarFacturacionInternadoShow();
?>