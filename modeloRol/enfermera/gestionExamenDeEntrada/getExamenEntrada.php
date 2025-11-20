<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionExamenDeEntrada\getExamenEntrada.php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlExmenEntrada.php');

$objControl = new controlExmenEntrada();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $examenId = $_GET['id'];
    
    if (!is_numeric($examenId)) {
        $objMensaje->mensajeSistemaShow("ID de Examen Clínico no válido.", "./indexExamenEntrada.php", "error"); 
    } else {
        $objControl->eliminarExamen((int)$examenId);
    }
} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: ./indexExamenEntrada.php");
    exit();
}
?> 