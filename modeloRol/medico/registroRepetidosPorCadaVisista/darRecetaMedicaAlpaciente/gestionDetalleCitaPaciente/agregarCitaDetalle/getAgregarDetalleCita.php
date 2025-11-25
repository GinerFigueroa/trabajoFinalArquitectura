<?php

session_start();
include_once('../../../../../../shared/mensajeSistema.php');
// Se incluye el Mediator
include_once('./controlAgregarDetalleCita.php'); 

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarDetalleCita(); // El Mediator

// Verificar que el usuario tenga sesión activa y sea médico (Lógica de acceso)
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede agregar detalles de recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// Recolección de datos y llamada al Mediator
if (isset($_POST['btnAgregar'])) {
    $data = [
        'action'          => 'agregar', 
        'idReceta'        => $_POST['idReceta'] ?? null,
        'medicamento'     => $_POST['medicamento'] ?? '',
        'dosis'           => $_POST['dosis'] ?? '',
        'frecuencia'      => $_POST['frecuencia'] ?? '',
        'duracion'        => $_POST['duracion'] ?? null,
        'notas'           => $_POST['notas'] ?? null,
        // Se pasa el ID del médico logueado para que el Command verifique la propiedad de la receta.
        'idUsuarioMedico' => $_SESSION['id_usuario'] ?? null, 
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('agregar', $data);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de registro.', 
        '../indexDetalleCita.php', 
        'error'
    );
}
?>