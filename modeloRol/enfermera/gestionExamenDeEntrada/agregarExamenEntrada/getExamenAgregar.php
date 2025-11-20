<?php

session_start();

include_once('./controlExamenAgregar.php');
$objControl = new controlExamenAgregar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar datos
    $historiaClinicaId = isset($_POST['historia_clinica_id']) ? (int)$_POST['historia_clinica_id'] : null;
    $peso = isset($_POST['peso']) ? (float)$_POST['peso'] : null;
    $talla = isset($_POST['talla']) ? (float)$_POST['talla'] : null;
    $pulso = isset($_POST['pulso']) ? $_POST['pulso'] : '';
    // idEnfermero puede ser NULL
    $idEnfermero = isset($_POST['id_enfermero']) && !empty($_POST['id_enfermero']) ? (int)$_POST['id_enfermero'] : null;

    // Llamar al controlador
    $objControl->registrarExamen($historiaClinicaId, $peso, $talla, $pulso, $idEnfermero);
} else {
    header("Location: ./indexExamenAgregar.php");
    exit();
}
?>