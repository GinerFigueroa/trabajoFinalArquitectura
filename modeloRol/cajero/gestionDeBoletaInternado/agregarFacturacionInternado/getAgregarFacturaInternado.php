<?php
// Directorio: /controlador/facturacionInternado/agregarFacturacionInternado/getAgregarFacturaInternado.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
// Incluye el Mediator/Controlador refactorizado
include_once('./controlAgregarFacturaInternado.php');

$objMensaje = new mensajeSistema();
// Instancia del Mediator
$objControl = new controlAgregarFacturaInternado();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado.", "../indexFacturacionInternadoPDF.php", "error");
    exit();
}

// Recolección de todos los datos del formulario POST
$data = [
    'id_internado' => isset($_POST['id_internado']) ? (int)$_POST['id_internado'] : null,
    'fecha_emision' => $_POST['fecha_emision'] ?? null,
    'dias_internado' => isset($_POST['dias_internado']) ? (int)$_POST['dias_internado'] : null,
    'costo_habitacion' => $_POST['costo_habitacion'] ?? 0.00,
    'costo_tratamientos' => $_POST['costo_tratamientos'] ?? 0.00,
    'costo_medicamentos' => $_POST['costo_medicamentos'] ?? 0.00,
    'costo_otros' => $_POST['costo_otros'] ?? 0.00,
    'total' => $_POST['total'] ?? null,
    'estado' => $_POST['estado'] ?? null,
];

// MEDIATOR: Invoca el método coordinador con la acción 'agregar' y los datos.
$objControl->ejecutarComando('agregar', $data);

// Si el comando falla o tiene éxito, el control ya habrá manejado el mensaje del sistema y la redirección.
?>