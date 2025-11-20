<?php

session_start();

include_once('./controlAgregarEvolucionPaciente.php');
$objControl = new controlAgregarEvolucionPaciente();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar datos
    $idInternado = isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null;
    $idMedico = isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null;
    // idEnfermera puede ser NULL si no se selecciona
    $idEnfermera = isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null;
    $evolucion = isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '';
    $tratamiento = isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '';

    // Llamar al controlador
    $objControl->registrarEvolucion($idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ./indexaAgregarEvolucionPaciente.php");
    exit();
}
?>