<?php

session_start();
include_once('../../../../../../shared/mensajeSistema.php');
include_once('./controlEditarRecetaMedica.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlEditarRecetaMedica();

// Recolectar datos del formulario POST
if (isset($_POST['btnEditar'])) {
    $data = [
        'idReceta' => $_POST['idReceta'] ?? null,
        'historiaClinicaId' => $_POST['historiaClinicaId'] ?? null,
        'fecha' => $_POST['fecha'] ?? null,
        'indicacionesGenerales' => $_POST['indicacionesGenerales'] ?? '',
        'idUsuarioLogueado' => $_SESSION['id_usuario'] ?? null,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción (editar) y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('editar', $data);

} else {
    // Acceso directo no permitido
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Debe enviar el formulario de edición.', 
        '../indexRecetaMedica.php', 
        'error'
    );
}
?>