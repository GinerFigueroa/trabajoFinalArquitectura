<?php
session_start();

// Validar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./indexRecordatorioPaciente.php");
    exit();
}

// Validar permisos
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    include_once('../../../shared/mensajeSistema.php');
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow(
        "No tiene permisos para acceder a esta función",
        "../index.php",
        "error"
    );
    exit();
}

// Redirigir según la acción
if (isset($_POST['btnGestionTelegram'])) {
    header("Location: ../editarRecordatorioPaciente/indexRegistrarRecordatorioPaciente.php");
    exit();
} elseif (isset($_POST['btnEnviarRecordatorios'])) {
    header("Location: ../recordatorioPacienteParaSuCitas/indexRecordatorio.php");
    exit();
} elseif (isset($_POST['btnRegistrarNuevo'])) {
    header("Location: ../registrarRecordatorioPaciente/indexRegistrarNuevoRecordatorio.php");
    exit();
} else {
    header("Location: ./indexRecordatorioPaciente.php");
    exit();
}
?>