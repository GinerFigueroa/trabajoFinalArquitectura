<?php
// C:\...\editarExamenEntrada\getExamenEditar.php
session_start();

include_once('./controlExamenEditar.php');
$objControl = new controlExamenEditar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar datos
    $examenId = isset($_POST['examen_id']) ? (int)$_POST['examen_id'] : null;
    $historiaClinicaId = isset($_POST['historia_clinica_id']) ? (int)$_POST['historia_clinica_id'] : null;
    $peso = isset($_POST['peso']) ? (float)$_POST['peso'] : null;
    $talla = isset($_POST['talla']) ? (float)$_POST['talla'] : null;
    $pulso = isset($_POST['pulso']) ? $_POST['pulso'] : '';
    $idEnfermero = isset($_POST['id_enfermero']) && !empty($_POST['id_enfermero']) ? (int)$_POST['id_enfermero'] : null;

    // Llamar al controlador
    $objControl->editarExamen($examenId, $historiaClinicaId, $peso, $talla, $pulso, $idEnfermero);
} else {
    header("Location: ./indexExamenEditar.php");
    exit();
}
?>