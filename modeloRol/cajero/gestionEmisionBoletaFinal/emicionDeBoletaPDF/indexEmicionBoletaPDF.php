<?php
// Directorio: /controlador/boleta/emicionDeBoletaPDF/indexEmicionBoletaPDF.php

// Incluir el archivo de control (Mediator)
include_once('./controlEmicionBoletaPDF.php');

// Recolectar datos de la solicitud (Invoker)
$data = [
    'action' => 'generarPDF',
    // Usamos 'id_boleta' tal como se espera en el GET
    'id_boleta' => $_GET['id_boleta'] ?? null, 
];

// Instanciar el Mediator/Controlador
$obj = new controlEmicionBoletaPDF();

// Delegar la ejecución al Mediator
$obj->ejecutarComando('generarPDF', $data);
?>