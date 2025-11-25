<?php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarHistorialMedico.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlEditarHistorialPaciente();

if (isset($_POST['btnActualizar'])) {
    // Recoger datos
    $data = [
        // Atributo: registro_medico_id
        'registro_medico_id' => $_POST['registro_medico_id'] ?? null,
        // Atributo: motivo_consulta
        'motivo_consulta' => $_POST['motivo_consulta'] ?? '',
        // Atributo: enfermedad_actual
        'enfermedad_actual' => $_POST['enfermedad_actual'] ?? '',
        // Atributo: tiempo_enfermedad
        'tiempo_enfermedad' => $_POST['tiempo_enfermedad'] ?? '',
        // Atributo: signos_sintomas
        'signos_sintomas' => $_POST['signos_sintomas'] ?? '',
        // Atributo: riesgos
        'riesgos' => $_POST['riesgos'] ?? '',
        // Atributo: motivo_ultima_visita
        'motivo_ultima_visita' => $_POST['motivo_ultima_visita'] ?? '',
        // Atributo: ultima_visita_medica
        'ultima_visita_medica' => $_POST['ultima_visita_medica'] ?? null,
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: ejecutarComando
    $objControl->ejecutarComando('actualizar', $data);

} else {
    // Si no es la acción esperada, redirigir
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialMedico.php', 'systemOut', false);
}
?>