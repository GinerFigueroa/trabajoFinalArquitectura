<?php
// FILE: indexOdenPDF.php (Punto de entrada)

include_once('./controlOrdenPDF.php');
$obj = new controlOrdenPDF();
// MÉTODO
$obj->generarPDF();
?>