<?php


include_once('./formEditarEmisionBoleta.php');
    
$obj = new formEditarEmisionBoleta();
$obj->formEditarEmisionBoletaShow(); // Llama sin parámetros, el form obtiene el ID de GET
?>