<?php
// Archivo: getAgregarNuevoDocumento.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarDocumento.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnRegistrar'])) {
    
    // PATRÓN BUILDER (Construcción del DTO/Array de Datos)
    // Se incluye el archivo subido en el array de datos
    $datos = [
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'tipo' => $_POST['tipo'] ?? null,
        'nombre' => $_POST['nombre'] ?? null,
        'notas' => $_POST['notas'] ?? '',
        'archivo' => $_FILES['archivo'] ?? null // Datos del archivo
    ];

    // DISPATCHER: Crea el Command y lo ejecuta
    $objControl = new controlAgregarDocumento();
    $objControl->registrarDocumentoCommand($datos); // Ejecuta el Command
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexDocumento.php', 'systemOut', false);
}
?>