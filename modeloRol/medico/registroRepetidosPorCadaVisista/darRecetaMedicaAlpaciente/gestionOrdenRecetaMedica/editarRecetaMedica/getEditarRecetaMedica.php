<?php
session_start();
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./controlEditarRecetaMedica.php');

$objMensaje = new mensajeSistema();
$objControl = new controlEditarRecetaMedica();

// Verificar que el usuario tenga sesión activa y sea médico
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede editar recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// Manejo del formulario de edición
if (isset($_POST['btnEditar'])) {
    // Recoger y validar datos del formulario
    $idReceta = $_POST['idReceta'] ?? null;
    $historiaClinicaId = $_POST['historiaClinicaId'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $indicacionesGenerales = $_POST['indicacionesGenerales'] ?? '';

    // Validaciones básicas
    if (empty($idReceta) || empty($historiaClinicaId) || empty($fecha) || empty($indicacionesGenerales)) {
        $objMensaje->mensajeSistemaShow(
            '❌ Todos los campos marcados con (*) son obligatorios.', 
            './indexEditarRecetaMedica.php?id=' . $idReceta, 
            'error'
        );
        exit();
    }

    // Validar que la historia clínica existe
    if (!$objControl->validarHistoriaClinica($historiaClinicaId)) {
        $objMensaje->mensajeSistemaShow(
            '❌ La historia clínica seleccionada no es válida.', 
            './indexEditarRecetaMedica.php?id=' . $idReceta, 
            'error'
        );
        exit();
    }

    // Validar que la receta existe y pertenece al médico
    if (!$objControl->validarPropiedadReceta($idReceta)) {
        $objMensaje->mensajeSistemaShow(
            '❌ No tiene permisos para editar esta receta o la receta no existe.', 
            '../indexRecetaMedica.php', 
            'error'
        );
        exit();
    }

    // Llamar al controlador para procesar la edición
    $objControl->editarReceta($idReceta, $historiaClinicaId, $fecha, $indicacionesGenerales);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de edición.', 
        '../indexRecetaMedica.php', 
        'error'
    );
}
?>