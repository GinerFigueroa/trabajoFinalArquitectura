

<?php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlExamenAgregar.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlExamenAgregar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recolectar datos de la solicitud (Invoker)
    $data = [
        'action' => 'registrar', // Nueva acción para el Factory
        'historia_clinica_id' => $_POST['historia_clinica_id'] ?? null,
        'peso' => $_POST['peso'] ?? null,
        'talla' => $_POST['talla'] ?? null,
        'pulso' => $_POST['pulso'] ?? null,
        // idEnfermero puede ser NULL, lo manejamos en el DTO
        'id_enfermero' => $_POST['id_enfermero'] ?? null,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // El control ahora usa un método unificado para la ejecución.
    $objControl->ejecutarComando('registrar', $data);

} else {
    // Si no es un POST válido
    $objMensaje->mensajeSistemaShow("Acceso denegado o método no permitido.", "./indexExamenAgregar.php", "systemOut", false);
}
?>