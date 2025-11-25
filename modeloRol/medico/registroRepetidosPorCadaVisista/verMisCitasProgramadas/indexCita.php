<?php
// Directorio: /vista/cita/indexCitas.php

session_start();
// Atributo: $obj (Instancia de la Vista)
include_once('./formTotalCitas.php'); 
$obj = new formTotalCitas();
// Método: formTotalCitasShow (Método del Template)
$obj->formTotalCitasShow();
?>