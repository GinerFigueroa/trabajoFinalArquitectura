<?php

// indexFacturacionPdf.php

include_once('getFacturacionInternadoPDF.php');

$controlador = new controlFacturacionInternadoPDF();
$controlador->generarPDF();

?>