<?php

session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlCitas.php'); // Incluye el controlador con las clases Command y Factory

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Invoker y Mediator/Receptor)
$objControl = new controlCitas();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $idCita = (int)($_GET['id'] ?? 0);
    $action = $_GET['action'];
    
    if ($idCita <= 0 || !in_array($action, ['confirmar', 'cancelar'])) {
        $objMensaje->mensajeSistemaShow("ID de cita o acción no válida.", "./indexCita.php", "error");
        exit;
    }

    try {
        // PATRÓN FACTORY METHOD: Creación del Command
        $comando = CitasFactory::crearComando($action, $idCita, $objControl);
        
        // PATRÓN COMMAND: Ejecución
        // Método: ejecutar
        $comando->ejecutar(); 

    } catch (Exception $e) {
        $objMensaje->mensajeSistemaShow("Error en la operación: " . $e->getMessage(), "./indexCita.php", "error");
    }

} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "./indexCita.php", "error");
}
?>