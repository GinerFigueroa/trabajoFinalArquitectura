<?php

include_once('./ControlConsentimientoInformadoPDF.php');

$obj = new ControlConsentimientoInformadoPDF();

// Llamamos a la función que inicia el proceso de obtención de datos y generación del PDF.
$obj->generarPDF();

?>
