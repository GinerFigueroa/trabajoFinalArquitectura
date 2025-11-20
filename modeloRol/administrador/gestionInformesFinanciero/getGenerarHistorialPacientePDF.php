<?php
session_start();

include_once('./controlGenerarHistorialPacientePDF.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y validar datos del formulario
    $idPaciente = isset($_POST['idPaciente']) ? (int)$_POST['idPaciente'] : null;
    $tipoReporte = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : 'completo';
    $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : null;
    $fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : null;
    $incluirResumen = isset($_POST['incluirResumen']) ? true : false;
    $incluirDetalles = isset($_POST['incluirDetalles']) ? true : false;

    // Validar fechas para reporte personalizado
    if ($tipoReporte === 'personalizado' && (empty($fechaInicio) || empty($fechaFin))) {
        include_once('../../../shared/mensajeSistema.php');
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow("Para reporte personalizado debe seleccionar fechas de inicio y fin.", "./formGenerarHistorialPacientePDF.php", "error");
        exit();
    }

    // Llamar al controlador
    $objControl = new controlGenerarHistorialPacientePDF();
    $objControl->generarHistorialPDF($idPaciente, $tipoReporte, $fechaInicio, $fechaFin, $incluirResumen, $incluirDetalles);
} else {
    // Redireccionar si no es POST
    header("Location: ./formGenerarHistorialPacientePDF.php");
    exit();
}
?>