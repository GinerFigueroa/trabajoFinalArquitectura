<?php
session_start();

// Incluir el controlador
include_once('./controlEditarRecordatorioPaciente.php');

// Obtener la acción del parámetro POST
$action = $_POST['action'] ?? '';

// Crear instancia del controlador
$objControl = new controlEditarRecordatorioPaciente();

// Procesar según la acción
switch ($action) {
    case 'probar_chat':
        $objControl->probarChat();
        break;
        
    case 'enviar_mensaje_personal':
        $objControl->enviarMensajePersonal();
        break;
        
    case 'cargar_formulario':
        $objControl->cargarFormularioEdicion();
        break;
        
    case 'guardar_edicion':
        $objControl->guardarEdicion();
        break;
        
    case 'cambiar_estado':
        $objControl->cambiarEstado();
        break;
        
    case 'eliminar_registro':
        $objControl->eliminarRegistro();
        break;
        
    case 'buscar_registros':
        $objControl->buscarRegistros();
        break;
        
    default:
        // Si no hay acción válida, redirigir con mensaje de error
        include_once('../../../../shared/mensajeSistema.php');
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow(
            'Acción no válida o no especificada.', 
            './indexEditarRecordatorioPaciente.php', 
            'error'
        );
        break;
}
?>