
<?php
// Directorio: /controlador/gestionDetalleCitaPaciente/getEditarDetalleCita.php

session_start();
include_once('../../../../../../shared/mensajeSistema.php');
// Se sigue incluyendo el Mediator
include_once('./controlEditarDetalleCita.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlEditarDetalleCita();

// Verificar que el usuario tenga sesión activa y sea médico (Lógica de acceso)
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede editar detalles de recetas.', 
        '../../../../index.php', 
        'error'
    );
    exit();
}

// ----------------------------------------------------
// CAMBIO CLAVE: Recolección de datos y llamada al Mediator
// ----------------------------------------------------
if (isset($_POST['btnEditar'])) {
    $data = [
        'action'        => 'editar', // Definimos la acción
        'idDetalle'     => $_POST['idDetalle'] ?? null,
        'medicamento'   => $_POST['medicamento'] ?? '',
        'dosis'         => $_POST['dosis'] ?? '',
        'frecuencia'    => $_POST['frecuencia'] ?? '',
        'duracion'      => $_POST['duracion'] ?? null,
        'notas'         => $_POST['notas'] ?? null,
        'idUsuarioMedico' => $_SESSION['id_usuario'] ?? null, // Pasamos el ID del médico logueado
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // El Mediador asume la responsabilidad de la validación de la lógica.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('editar', $data);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de edición.', 
        '../indexDetalleCita.php', 
        'error'
    );
}
?>