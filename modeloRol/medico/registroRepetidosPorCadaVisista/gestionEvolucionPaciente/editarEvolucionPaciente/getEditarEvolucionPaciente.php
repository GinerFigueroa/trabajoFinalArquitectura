<?php
// getEditarEvolucionPaciente.php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarEvolucionPaciente.php');

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Método no permitido.", "../indexEvolucionPaciente.php", "error");
    exit();
}

// Validar que todos los campos requeridos estén presentes
$camposRequeridos = ['id_evolucion', 'historia_clinica_id', 'nota_subjetiva'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow("Faltan campos obligatorios: " . $campo, "../editarEvolucionPaciente/indexEvolucionPaciente.php?evo_id=" . ($_POST['id_evolucion'] ?? ''), "error");
        exit();
    }
}

// Recoger y limpiar datos
$idEvolucion = (int)$_POST['id_evolucion'];
$historiaClinicaId = (int)$_POST['historia_clinica_id'];
$notaSubjetiva = trim($_POST['nota_subjetiva']);
$notaObjetiva = isset($_POST['nota_objetiva']) ? trim($_POST['nota_objetiva']) : '';
$analisis = isset($_POST['analisis']) ? trim($_POST['analisis']) : '';
$planDeAccion = isset($_POST['plan_de_accion']) ? trim($_POST['plan_de_accion']) : '';

// Validar ID de evolución
if ($idEvolucion <= 0) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("ID de evolución no válido.", "../indexEvolucionPaciente.php", "error");
    exit();
}

// Llamar al controlador
$objControl = new controlEditarEvolucionPaciente();
$objControl->editarEvolucion($idEvolucion, $notaSubjetiva, $notaObjetiva, $analisis, $planDeAccion);
?>