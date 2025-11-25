<?php

session_start();

include_once('./controlAgregarEvolucionPaciente.php');
// Atributo: `$objControl` (El Mediator)
$objControl = new controlAgregarEvolucionPaciente();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar datos
    $data = [
        // Atributo: `$idInternado`
        'idInternado' => isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null,
        // Atributo: `$idMedico` (ID de usuario)
        'idMedico' => isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null,
        // Atributo: `$idEnfermera` (ID de usuario, opcional)
        'idEnfermera' => isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null,
        // Atributo: `$evolucion`
        'evolucion' => isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '',
        // Atributo: `$tratamiento`
        'tratamiento' => isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '',
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('agregar', $data);
    
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ./indexaAgregarEvolucionPaciente.php");
    exit();
}
?>