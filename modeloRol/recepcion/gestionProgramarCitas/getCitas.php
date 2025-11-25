<?php
// Archivo: getCitas.php (Maneja acciones como 'eliminar')

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlCitas.php'); // Incluye el Controlador/Mediator

// ==========================================================
// PATRÓN: FRONT CONTROLLER / COMMAND (Invocador) 🛡️📦
// ==========================================================

$objControl = new controlCitas(); // Atributo: Instancia del Mediator
$objMensaje = new mensajeSistema(); // Atributo: Instancia de Utilidad

// Lógica del Front Controller: Determinar la acción y recopilar datos
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $request = [
        'action' => $_GET['action'],
        'id' => $_GET['id'] // Atributo: Dato de entrada
    ];
    
    // 📦 COMMAND INVOCADOR: Delega la solicitud completa al Mediator.
    // Ejemplo Método: eliminarCita(array $request)
    $objControl->eliminarCita($request);

} else {
    // Manejo de Acceso Denegado
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexCita.php", "systemOut", false);
}
?>