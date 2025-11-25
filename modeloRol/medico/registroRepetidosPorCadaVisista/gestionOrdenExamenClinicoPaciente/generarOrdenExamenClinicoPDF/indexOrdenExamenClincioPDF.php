<?php

// Atributo: $obj (Instancia del Controlador)
include_once('./controlExamenClinicoPDF.php');
$obj = new controlExamenClinicoPDF();

// Método: generarPDF (Inicio de la secuencia)
$obj->generarPDF();
?>