<?php
// Directorio: /controlador/gestionOrdenExamenClinico/agregarExamenClinico/getAgregarExamenClinico.php

session_start();

include_once('./controlAgregarExamenClinico.php');
include_once('../../../../../shared/mensajeSistema.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarExamenClinico();

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje->mensajeSistemaShow("Método no permitido.", "../indexOrdenExamenClinico.php", "error");
    exit();
}

// Recoger y limpiar datos (Invoker)
$data = [
    'action' => 'agregar', // Acción que define el Command a crear
    'historia_clinica_id' => $_POST['historia_clinica_id'] ?? null,
    'id_medico'           => $_POST['id_medico'] ?? null, // id_usuario del médico
    'fecha'               => $_POST['fecha'] ?? null,
    'tipo_examen'         => $_POST['tipo_examen'] ?? null,
    'indicaciones'        => $_POST['indicaciones'] ?? '',
    'estado'              => $_POST['estado'] ?? 'Pendiente',
];

// MEDIATOR: Invoca el método coordinador con la acción y los datos.
// Se elimina toda la validación previa, moviéndola al Command.
$objControl->ejecutarComando($data['action'], $data);

// Nota: Las validaciones básicas de 'isset', 'empty' y 'is_numeric'
// son movidas al Command para centralizar la lógica de validación y negocio.
?>