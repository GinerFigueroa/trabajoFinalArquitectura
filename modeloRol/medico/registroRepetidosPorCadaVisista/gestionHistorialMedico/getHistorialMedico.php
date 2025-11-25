<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlHistorialMedico.php'); // Incluye el controlador con las clases Command y Factory

$objControl = new controlHistorialClinico();
$objMensaje = new mensajeSistema();
$urlRetorno = "./indexHistorialMedico.php";

// Manejo de la acción
$action = $_GET['action'] ?? null;
$params = $_GET; // Usamos todos los parámetros GET

if (empty($action)) {
    header("Location: {$urlRetorno}");
    exit();
}

try {
    // PATRÓN FACTORY METHOD: Creación del Command
    // Atributo: $comando (Instancia de Comando Concreto)
    $comando = HistorialCommandFactory::crearComando($action, $objControl, $params);
    
    // PATRÓN COMMAND: Ejecución
    // Método: ejecutar
    $comando->ejecutar(); 

} catch (Exception $e) {
    // Captura errores del Factory (acción inválida o parámetros faltantes/inválidos)
    $objMensaje->mensajeSistemaShow($e->getMessage(), $urlRetorno, 'error');
}
?>