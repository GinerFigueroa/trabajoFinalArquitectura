

<?php


session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlDocumentos.php'); // Incluimos el Mediator

$objControl = new controlDocumentos(); // El Mediator
$objMensaje = new mensajeSistema();

// PATRÓN: INVOKER (Maneja la solicitud y la delega)



if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idDocumento = $_GET['id'];
    $idUsuario = $_SESSION['id_usuario'] ?? null; // ID del usuario que intenta eliminar

    $data = [
        'action' => 'eliminar',
        'idDocumento' => $idDocumento,
        'idUsuario' => $idUsuario,
    ];
    
    // MEDIATOR: Invoca el método coordinador para ejecutar el comando
    $objControl->ejecutarComando('eliminar', $data);

} else {
    // Si no hay acción válida, redirige al listado
    $objMensaje->mensajeSistemaShow(
        '❌ Solicitud no válida.', 
        './indexDocumento.php', 
        'error'
    );
}
?>