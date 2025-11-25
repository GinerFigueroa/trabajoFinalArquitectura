<?php
// Directorio: /controlador/examenClinico/getOrdenExamenClinico.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlExamenClinico.php'); // Incluye el controlador con las clases Command y Factory

$objControl = new controlExamenClinico();
$objMensaje = new mensajeSistema();
$urlRetorno = "./indexOrdenExamenClinico.php";


if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id_orden'])) {
    $idOrden = (int)$_GET['id_orden'];
    $action = $_GET['action'];

    if ($idOrden <= 0) {
        $objMensaje->mensajeSistemaShow("ID de orden no válido.", $urlRetorno, "error");
        exit;
    }
    
    try {
        // PATRÓN FACTORY METHOD: Creación del Command
        // Atributo: $comando (Instancia de Comando)
        $comando = ExamenFactory::crearComando($action, $idOrden, $objControl);
        
        // PATRÓN COMMAND: Ejecución
        // Método: ejecutar
        $comando->ejecutar(); 

    } catch (Exception $e) {
        $objMensaje->mensajeSistemaShow("Error en la operación: " . $e->getMessage(), $urlRetorno, "error");
    }

} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: " . $urlRetorno);
    exit();
}
?>