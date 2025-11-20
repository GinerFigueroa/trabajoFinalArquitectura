<?php
session_start();
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./controlEditarDetalleCita.php');

$objMensaje = new mensajeSistema();
$objControl = new controlEditarDetalleCita();

// Verificar que el usuario tenga sesión activa y sea médico
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede editar detalles de recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// Manejo del formulario de edición
if (isset($_POST['btnEditar'])) {
    // Recoger y validar datos del formulario
    $idDetalle = $_POST['idDetalle'] ?? null;
    $medicamento = $_POST['medicamento'] ?? '';
    $dosis = $_POST['dosis'] ?? '';
    $frecuencia = $_POST['frecuencia'] ?? '';
    $duracion = $_POST['duracion'] ?? null;
    $notas = $_POST['notas'] ?? null;

    // Validaciones básicas
    if (empty($idDetalle) || empty($medicamento) || empty($dosis) || empty($frecuencia)) {
        $objMensaje->mensajeSistemaShow(
            '❌ Todos los campos marcados con * son obligatorios.', 
            './indexEditarDetalleCita.php?id=' . $idDetalle, 
            'error'
        );
        exit();
    }

    // Validar que el detalle existe y pertenece al médico
    $idUsuarioMedico = $_SESSION['id_usuario'];
    if (!$objControl->obtenerDetalle($idDetalle)) {
        $objMensaje->mensajeSistemaShow(
            '❌ El detalle de receta no existe.', 
            '../indexDetalleCita.php', 
            'error'
        );
        exit();
    }

    // Llamar al controlador para procesar la edición
    $objControl->editarDetalle($idDetalle, $medicamento, $dosis, $frecuencia, $duracion, $notas);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de edición.', 
        '../indexDetalleCita.php', 
        'error'
    );
}
?>