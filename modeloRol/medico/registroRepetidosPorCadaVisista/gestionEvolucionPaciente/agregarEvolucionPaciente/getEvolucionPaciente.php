<?php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPaciente.php');

$objControl = new controlEvolucionPaciente();
$objMensaje = new mensajeSistema();

// Manejo de la acción de REGISTRAR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'registrar') {
    
    // El Invoker llama al método coordinador del Mediator
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('registrar', $_POST);

} else {
    // Si la solicitud no es válida, redirige al formulario
    header("Location: ./formEvolucionPaciente.php?error=" . urlencode("Acción no válida"));
    exit();
}
?>