<?php

session_start();

include_once('./controlEditarHistorialPaciente.php'); // Incluimos el Mediator
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlEditarHistorialPaciente(); // El Mediator
$objMensaje = new mensajeSistema();

// Verificar sesión y rol (Asumiendo que un Médico/Admin puede editar)
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'] ?? 0, [1, 2])) {
    $objMensaje->mensajeSistemaShow(
        "❌ Acceso Denegado. Permisos insuficientes para editar la Historia Clínica.", 
        "../../../vista/login.php", 
        "error"
    ); 
    exit();
}

// Procesar el formulario (Patrón: Invoker)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnEditar'])) {
    
    // Recolección de datos
    $data = [
        'action' => 'editar',
        'historia_clinica_id' => $_POST['historia_clinica_id'] ?? null,
        'id_paciente' => $_POST['id_paciente'] ?? null,
        'dr_tratante_id' => $_POST['dr_tratante_id'] ?? null,
        'fecha_creacion' => $_POST['fecha_creacion'] ?? null,
        'id_usuario_editor' => $_SESSION['id_usuario'], // Quién ejecuta la edición
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('editar', $data);
    
} else {
    // Si se accede sin acción POST válida
    header("Location: ./indexEditarHistorialPaciente.php");
    exit();
}
?>