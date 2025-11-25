<?php

session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlHistorialClinico.php'); // Incluimos el Mediator

$objControl = new controlHistorialClinico(); // El Mediator
$objMensaje = new mensajeSistema();



$idMedicoLogueado = $_SESSION['id_usuario'];


// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idHistoria = $_GET['id'];
    
    // Recolección de datos
    $data = [
        'action' => 'eliminar',
        'idHistoria' => $idHistoria,
        'idMedico' => $idMedicoLogueado,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('eliminar', $data);

} else {
    // Si se accede sin acción válida
    header("Location: ./indexHistoriaClinica.php");
    exit();
}
?>