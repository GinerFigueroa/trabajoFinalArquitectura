<?php
session_start();
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarRecetaMedica.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarRecetaMedica();

// Verificar que el usuario tenga sesión activa y sea médico
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede registrar recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// Manejo del formulario de registro
if (isset($_POST['btnAgregar'])) {
    // Recoger y validar datos del formulario
    $historiaClinicaId = $_POST['historiaClinicaId'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $indicacionesGenerales = $_POST['indicacionesGenerales'] ?? '';

    // Validaciones básicas
    if (empty($historiaClinicaId) || empty($fecha) || empty($indicacionesGenerales)) {
        $objMensaje->mensajeSistemaShow(
            '❌ Todos los campos marcados con (*) son obligatorios.', 
            './indexAgregarRecetaMedica.php', 
            'error'
        );
        exit();
    }

    // Validar que la historia clínica existe
    if (!$objControl->validarHistoriaClinica($historiaClinicaId)) {
        $objMensaje->mensajeSistemaShow(
            '❌ La historia clínica seleccionada no es válida.', 
            './indexAgregarRecetaMedica.php', 
            'error'
        );
        exit();
    }

    // Llamar al controlador para procesar el registro
    $objControl->agregarReceta($historiaClinicaId, $fecha, $indicacionesGenerales);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de registro.', 
        '../indexRecetaMedica.php', 
        'error'
    );
}
?>