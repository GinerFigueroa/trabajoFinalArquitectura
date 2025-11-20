<?php
session_start();
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarDetalleCita.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarDetalleCita();

// Verificar que el usuario tenga sesión activa y sea médico
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede agregar detalles de recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// Manejo del formulario de registro
if (isset($_POST['btnAgregar'])) {
    // Recoger y validar datos del formulario
    $idReceta = $_POST['idReceta'] ?? null;
    $medicamento = $_POST['medicamento'] ?? '';
    $dosis = $_POST['dosis'] ?? '';
    $frecuencia = $_POST['frecuencia'] ?? '';
    $duracion = $_POST['duracion'] ?? null;
    $notas = $_POST['notas'] ?? null;

    // Validaciones básicas
    if (empty($idReceta) || empty($medicamento) || empty($dosis) || empty($frecuencia)) {
        $objMensaje->mensajeSistemaShow(
            '❌ Todos los campos marcados con * son obligatorios.', 
            './indexAgregarDetalleCita.php', 
            'error'
        );
        exit();
    }

    // Llamar al controlador para procesar el registro
    $objControl->agregarDetalle($idReceta, $medicamento, $dosis, $frecuencia, $duracion, $notas);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de registro.', 
        '../indexDetalleCita.php', 
        'error'
    );
}
?>