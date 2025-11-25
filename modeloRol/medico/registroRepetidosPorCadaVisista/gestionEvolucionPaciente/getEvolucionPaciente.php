<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPaciente.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlEvolucionPaciente();

// Manejo de la acción de ELIMINAR EVOLUCIÓN
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['evo_id'])) {
    
    $data = [
        // Atributo: id_evolucion
        'id_evolucion' => $_GET['evo_id'], 
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: ejecutarComando
    $objControl->ejecutarComando('eliminar', $data);

} else {
    // Si no hay acción válida, redirige al listado principal
    $objMensaje->mensajeSistemaShow('Acción no válida o faltan parámetros.', './indexEvolucionPaciente.php', 'systemOut', false);
}
?>