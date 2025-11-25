<?php
// Script de Acción (index.php o similar)

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarHistorialMedico.php'); // Carga la clase controlAgregarHistorialPaciente

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlAgregarHistorialPaciente();

if (isset($_POST['btnGuardar'])) { // ESTA ES LA CLAVE PARA EVITAR EL "ACCESO DENEGADO"
    // Recoger datos
    $data = [
        'historia_clinica_id' => $_POST['historia_clinica_id'] ?? null,
        'motivo_consulta' => $_POST['motivo_consulta'] ?? '',
        'enfermedad_actual' => $_POST['enfermedad_actual'] ?? '',
        'tiempo_enfermedad' => $_POST['tiempo_enfermedad'] ?? '',
        'signos_sintomas' => $_POST['signos_sintomas'] ?? '',
        'riesgos' => $_POST['riesgos'] ?? '',
        'motivo_ultima_visita' => $_POST['motivo_ultima_visita'] ?? '',
        'ultima_visita_medica' => $_POST['ultima_visita_medica'] ?? null,
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('registrar', $data);

} else {
    // Si no es la acción esperada, redirigir
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialMedico.php', 'systemOut', false);
}
?>