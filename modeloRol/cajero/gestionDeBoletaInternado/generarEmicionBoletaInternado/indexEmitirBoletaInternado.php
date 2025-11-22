<?php

// indexFacturacionPdf.php

include_once('controlEmitirBoletaInternado.php');

$controlador = new controlFacturacionInternadoPDF();
$controlador->generarPDF();

?>