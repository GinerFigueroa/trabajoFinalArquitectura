<?php


include_once('../../../shared/mensajeSistema.php');
include_once('./controlRecordatorioPaciente.php');

$objMensaje = new mensajeSistema();

// Obtener la acción desde GET o POST
$action = $_REQUEST['action'] ?? '';

if (empty($action)) {
    $objMensaje->mensajeSistemaShow("Acción no especificada", "./indexRecordatorioPaciente.php", "error");
    exit();
}

$objControl = new controlRecordatorioPaciente();

// Ejecutar la acción correspondiente
switch ($action) {
    case 'probar_mensajes':
        $objControl->procesarMensajesPrueba();
        break;
        
    case 'enviar_alerta_masiva':
        $mensaje = $_POST['mensaje_alerta'] ?? '';
        $objControl->procesarAlertaMasiva($mensaje);
        break;
        
  case 'enviar_recordatorios_citas': 
    // Usar método original temporalmente
    $objControl->enviarRecordatoriosCitasDelDia();
    break;
        
    case 'verificar_estado':
        $objControl->verificarEstadoSistema();
        break;
        
    case 'generar_reporte':
        $objControl->generarReporteSistema();
        break;
        
    case 'depurar_consultas': // NUEVA ACCIÓN PARA DEPURAR
        $objControl->probarConsultasCitas();
        break;
        
    default:
        $objMensaje->mensajeSistemaShow("Acción no válida: $action", "./indexRecordatorioPaciente.php", "error");
        break;
}