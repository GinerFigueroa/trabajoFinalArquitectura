<?php


include_once('./controlEmicionBoletaPDF.php');

// El controlador se encarga de obtener el ID de GET, validarlo y generar el PDF.
$obj = new controlEmicionBoletaPDF();
$obj->generarPDF();
?>