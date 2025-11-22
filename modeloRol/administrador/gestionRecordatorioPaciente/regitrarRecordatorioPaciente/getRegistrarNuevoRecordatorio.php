<?php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlRegistrarNuevoRecordatorio.php');

$objMensaje = new mensajeSistema();

// Obtener la acción
$action = $_POST['action'] ?? '';

// Crear instancia del controlador
$objControl = new controlRegistrarNuevoRecordatorio();

// Procesar según la acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btnRegistrarPaciente'])) {
        $objControl->procesarRegistroPaciente();
    } elseif ($action === 'probar_chat_id') {
        $objControl->probarChatId();
    } else {
        $objMensaje->mensajeSistemaShow("Acción no válida", "./indexRegistrarNuevoRecordatorio.php", "error");
    }
} else {
    $objMensaje->mensajeSistemaShow("Método no permitido", "./indexRegistrarNuevoRecordatorio.php", "error");
}
?>