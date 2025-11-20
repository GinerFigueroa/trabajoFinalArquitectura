<?php
// C:\...\gestionHistoriaClinica\getHistorialClinica.php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlHistorialClinico.php');

$objControl = new controlHistorialClinico();
$objMensaje = new mensajeSistema();

// Validar Médico Logueado
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 2) {
    $objMensaje->mensajeSistemaShow("Debe iniciar sesión como Médico.", "../../../vista/login.php", "error"); 
    exit();
}
$idMedicoLogueado = $_SESSION['id_usuario'];


// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idHistoria = $_GET['id'];
    
    if (!is_numeric($idHistoria)) {
        $objMensaje->mensajeSistemaShow("ID de Historia Clínica no válido.", "./indexHistoriaClinica.php", "error"); 
    } else {
        $objControl->eliminarHistoria((int)$idHistoria, $idMedicoLogueado);
    }
} else {
    // Si se accede sin acción válida
    header("Location: ./indexHistoriaClinica.php");
    exit();
}
?> 