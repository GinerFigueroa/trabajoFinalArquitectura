<?php
// Directorio: /vista/historialAnemia/indexHistorialAnemia.php

session_start();

// Atributo: `$obj` (Instancia de la Vista)
include_once('./formHistorialAnemia.php');
$obj = new formHistorialAnemia();
// Método: `formHistorialAnemiaShow` (Muestra la Vista)
$obj->formHistorialAnemiaShow();
?>