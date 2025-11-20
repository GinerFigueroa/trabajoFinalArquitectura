<?php

// indexFacturacionPdf.php

include_once('getEmitirBoletaInternado.php');

$controlador = new controlFacturacionInternadoPDF();
$controlador->generarPDF();

?>