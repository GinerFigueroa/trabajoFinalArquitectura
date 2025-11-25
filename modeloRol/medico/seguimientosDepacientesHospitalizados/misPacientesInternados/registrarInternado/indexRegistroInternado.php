<?php
include_once('./formRegistroInternado.php');

// Patrón FACTORY SIMPLE (Implicitamente) para crear la instancia de la vista
$obj = new formRegistroInternado(); 
$obj->formRegistroInternadoShow();
?>