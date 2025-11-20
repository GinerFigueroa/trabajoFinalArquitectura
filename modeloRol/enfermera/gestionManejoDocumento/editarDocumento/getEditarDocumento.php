
<?php
// Archivo: getEditarDocumento.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarDocumento.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    
    // PATRÓN BUILDER (Construcción del DTO/Array de Datos)
    $datos = [
        'idDocumento' => $_POST['idDocumento'] ?? null,
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'tipo' => $_POST['tipo'] ?? null,
        'nombre' => $_POST['nombre'] ?? null,
        'notas' => $_POST['notas'] ?? ''
    ];

    // DISPATCHER: Crea el Command y lo ejecuta
    $objControl = new controlEditarDocumento();
    $objControl->editarDocumentoCommand($datos); // Ejecuta el Command
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexDocumento.php', 'systemOut', false);
}
?>