<?php

// Atributo: `idAnamnesis`
// Método: `generarPDF` (ejecuta el proceso)
include_once('./controlHistorialAnemiaPDF.php');
$obj = new controlHistorialAnemiaPDF();
$obj->generarPDF();
?>