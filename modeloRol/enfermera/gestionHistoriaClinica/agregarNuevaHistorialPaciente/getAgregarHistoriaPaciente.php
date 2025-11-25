<?php

session_start();

include_once('./controlAgregarHistorialPaciente.php'); // Incluimos el Mediator
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlAgregarHistorialPaciente(); // El Mediator
$objMensaje = new mensajeSistema();

// Verificar solo que haya un usuario logueado
if (!isset($_SESSION['id_usuario'])) {
    $objMensaje->mensajeSistemaShow(
        "❌ Debe iniciar sesión para realizar esta acción.", 
        "../../../vista/login.php", 
        "error"
    ); 
    exit();
}

// Procesar el formulario (Patrón: Invoker)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recolección de datos
    $data = [
        'action' => 'agregar',
        'id_paciente' => $_POST['id_paciente'] ?? null,
        // El personal tratante (dr_tratante_id) es el usuario logueado en este flujo
        'dr_tratante_id' => $_POST['dr_tratante_id'] ?? null,
        // La fecha de creación viene como un campo oculto, pero se puede sobrescribir aquí
        'fecha_creacion' => $_POST['fecha_creacion'] ?? date("Y-m-d"), 
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('agregar', $data);
    
} else {
    // Si se accede sin acción POST válida
    header("Location: ./indexAgregarHistorialPaciente.php");
    exit();
}
?>