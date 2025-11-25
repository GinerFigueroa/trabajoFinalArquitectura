<?php
// Directorio: /controlador/facturacionInternado/getFacturacionInternado.php

session_start();

include_once('../../../shared/mensajeSistema.php');
// Incluye el Mediator/Controlador refactorizado
include_once('./controlFacturacionInternado.php'); 

$objMensaje = new mensajeSistema();
// Instancia del Mediator
$objControl = new controlFacturacionInternado();

$action = $_GET['action'] ?? null;
// Recolección y saneamiento de datos
$data = [
    'id' => isset($_GET['id']) ? (int)$_GET['id'] : null,
    // Otras propiedades necesarias para acciones futuras podrían ir aquí
];

$urlRedireccion = "./indexFacturacionInternado.php";

if (!$action || !$data['id']) {
    // Si no hay acción o ID, simplemente redirigir de vuelta.
    header("Location: {$urlRedireccion}");
    exit();
}

// MEDIATOR: Invoca el método coordinador con la acción y los datos.
$objControl->ejecutarComando($action, $data);

// Si el comando falla o tiene éxito, el control ya habrá manejado el mensaje del sistema y la redirección.
?>