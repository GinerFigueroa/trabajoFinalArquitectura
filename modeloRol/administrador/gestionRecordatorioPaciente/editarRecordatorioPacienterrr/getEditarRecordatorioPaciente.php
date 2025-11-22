<?php

// CORREGIDO: Incluir el controlador correcto
include_once('./controlEditarRecordatorioPaciente.php');

// Configurar header para JSON
header('Content-Type: application/json');

// Obtener la acción
$action = $_GET['action'] ?? '';

// Crear instancia del controlador
$objControl = new controlEditarRecordatorioPaciente();

// Procesar según la acción
switch ($action) {
    case 'obtener_chat':
        $objControl->obtenerChat();
        break;
        
    case 'editar_chat':
        $objControl->editarChat();
        break;
        
    case 'probar_mensaje':
        $objControl->probarMensaje();
        break;
        
    case 'desactivar_chat':
        $objControl->desactivarChat();
        break;
        
    case 'reactivar_chat':
        $objControl->reactivarChat();
        break;
        
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
?>