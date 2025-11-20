
<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlDocumento.php');

$objControl = new controlDocumentos();
$objMensaje = new mensajeSistema();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idDocumento = $_GET['id'];
    
    if (!is_numeric($idDocumento)) {
        $objMensaje->mensajeSistemaShow("ID de documento no válido.", "./indexDocumento.php", "systemOut", false);
    } else {
        // Ejecución del Command
        $objControl->eliminarDocumento($idDocumento);
    }
} else {
    // Si no hay acción válida, redirige al listado
    header('Location: ./indexDumento.php');
    exit();
}
?>