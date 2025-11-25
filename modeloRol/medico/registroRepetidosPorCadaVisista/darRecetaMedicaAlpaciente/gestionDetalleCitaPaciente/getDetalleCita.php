<?php
// Directorio: /controlador/gestionDetalleCitaPaciente/getDetalleCita.php

session_start();
// La ruta de mensajeSistema.php debe ser ajustada si es necesario.
include_once('../../../../shared/mensajeSistema.php');
// Incluir el Mediator/Controlador refactorizado
include_once('./controlDetalleCita.php');

$objMensaje = new mensajeSistema();

// Verificar que el usuario tenga sesión activa y sea médico (rol_id = 2)
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede gestionar detalles de recetas.', 
        '../../../index.php', 
        'error'
    );
    exit();
}

// Atributo: $objControl (El Mediator)
$objControl = new controlDetalleCita();

// Recolectar datos de la solicitud (Invoker)
$action = $_GET['action'] ?? null;
$idDetalle = $_GET['id'] ?? null;

if ($action === 'eliminar' && $idDetalle !== null && is_numeric($idDetalle)) {
    $data = [
        'action' => $action,
        'idDetalle' => (int)$idDetalle,
        // Añadir datos de sesión necesarios para la validación de propiedad en el Command
        'idUsuario' => $_SESSION['id_usuario']
    ];
    
    // MEDIATOR: Invoca el método coordinador (ejecutarComando)
    $objControl->ejecutarComando($action, $data);

} else {
    // Si la acción o el ID no son válidos
    $objMensaje->mensajeSistemaShow("Acceso denegado, acción no válida o ID incorrecto.", "./indexDetalleCita.php", "error");
}
?>