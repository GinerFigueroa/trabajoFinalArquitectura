<?php
// Directorio: /controlador/emisionBoleta/getAgregarEmisionBoleta.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
// Incluye el Mediator/Controlador refactorizado
include_once('./controlAgregarEmisionBoleta.php'); 

$objMensaje = new mensajeSistema();
// Instancia del Mediator
$objControl = new controlAgregarEmisionBoleta();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado.", "../../indexEmisionBoletaFinal.php", "error");
    exit();
}

// Recolección y saneamiento de datos (Preparación de la Data para el Factory)
$data = [
    'id_orden' => isset($_POST['id_orden']) ? (int)$_POST['id_orden'] : null,
    'numero_boleta' => trim($_POST['numero_boleta'] ?? ''),
    'tipo' => $_POST['tipo'] ?? null,
    // Aseguramos que sea un float para el DTO
    'monto_total' => isset($_POST['monto_total']) ? (float)$_POST['monto_total'] : null, 
    'metodo_pago' => $_POST['metodo_pago'] ?? null,
];

// Se define la acción a ejecutar
$action = 'emitir'; 

// MEDIATOR: Invoca el método coordinador con la acción y los datos.
// Se asume que la validación básica de campos obligatorios ahora se maneja en el DTO/Command.
$objControl->ejecutarComando($action, $data);

// No es necesario un 'else' si el control ya maneja todos los mensajes del sistema.
?>