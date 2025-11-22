<?php
// Archivo: getRecordatorioPaciente.php



include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecordatorioPaciente.php');

$objMensaje = new mensajeSistema();

// Obtener la acción desde GET o POST
$action = $_REQUEST['action'] ?? '';

// Validar que la acción existe
if (empty($action)) {
    $objMensaje->mensajeSistemaShow("Acción no especificada", "./indexRecordatorioPaciente.php", "error");
    exit();
}

// Crear instancia del controlador
$objControl = new controlRecordatorioPaciente();

// Ejecutar la acción correspondiente
switch ($action) {
    case 'probar_mensajes':
        // Acción original para mensajes de prueba fijos
        $objControl->procesarMensajesPrueba();
        break;
        
    case 'enviar_alerta_masiva':
        // NUEVA ACCIÓN: Recibe el mensaje de la caja de texto
        $mensaje = $_POST['mensaje_alerta'] ?? '';
        $objControl->procesarAlertaMasiva($mensaje);
        break;
        
    case 'verificar_estado':
        $objControl->verificarEstadoSistema();
        break;
        
    case 'generar_reporte':
        $objControl->generarReporteSistema();
        break;
        
    default:
        $objMensaje->mensajeSistemaShow("Acción no válida: $action", "./indexRecordatorioPaciente.php", "error");
        break;
}
?>