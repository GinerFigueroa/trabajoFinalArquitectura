<?php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarEvolucionPaciente.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlEditarEvolucionPaciente();

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['btnActualizar'])) {
    $objMensaje->mensajeSistemaShow("Método no permitido o acción no válida.", "../indexEvolucionPaciente.php", "error");
    exit();
}

// Recoger datos
$data = [
    // Atributo: id_evolucion
    'id_evolucion' => $_POST['id_evolucion'] ?? null,
    // Atributo: nota_subjetiva
    'nota_subjetiva' => $_POST['nota_subjetiva'] ?? '',
    // Atributo: nota_objetiva
    'nota_objetiva' => $_POST['nota_objetiva'] ?? '',
    // Atributo: analisis
    'analisis' => $_POST['analisis'] ?? '',
    // Atributo: plan_de_accion
    'plan_de_accion' => $_POST['plan_de_accion'] ?? '',
];

// MEDIATOR: Invoca el método coordinador con la acción y los datos.
// Método: ejecutarComando
$objControl->ejecutarComando('editar', $data);

?>