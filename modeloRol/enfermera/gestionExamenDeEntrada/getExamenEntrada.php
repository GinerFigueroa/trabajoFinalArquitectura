<?php

include_once('../../../shared/mensajeSistema.php');
include_once('./controlExmenEntrada.php'); // Incluimos el Mediator

$objControl = new controlExmenEntrada(); // El Mediator
$objMensaje = new mensajeSistema();


// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $examenId = $_GET['id'];
    
    // Recolección de datos
    $data = [
        'action' => 'eliminar',
        'examenId' => $examenId,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('eliminar', $data);

} else {
    // Si no hay acción válida, redirige al listado
    header("Location: ./indexExamenEntrada.php");
    exit();
}
?>