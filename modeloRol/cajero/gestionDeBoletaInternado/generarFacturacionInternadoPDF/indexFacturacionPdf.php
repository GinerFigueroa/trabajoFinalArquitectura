<?php

// indexFacturacionPdf.php

include_once('controlFacturacionInternadoPDF.php');

$controlador = new controlFacturacionInternadoPDF();
$controlador->generarPDF();

?>