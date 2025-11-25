El código original para editar el examen clínico se ha refactorizado para implementar los patrones Command, Factory y State.

La lógica de validación y la interacción con la capa de datos (DAO) se han movido a la clase EditarExamenCommand (Command Concreto). El archivo controlExamenEditar.php ahora actúa como Mediator y utiliza una Factory para crear el Command, y lee el State (mensaje de error o éxito) del Command para mostrar la respuesta apropiada.

Aquí están los archivos refactorizados:

1. ⚙️ Archivo getExamenEditar.php (Invoker)
Este archivo maneja la petición POST y delega la ejecución al Mediator (controlExamenEditar).

PHP

<?php
// Directorio: /controlador/gestionExamenDeEntrada/editarExamenEntrada/getExamenEditar.php

session_start();

include_once('./controlExamenEditar.php'); // Incluimos el Mediator
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlExamenEditar(); // El Mediator
$objMensaje = new mensajeSistema();

// PATRÓN: INVOKER (Maneja la solicitud y la delega)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y sanitizar datos directamente en el array de datos
    $data = [
        'action' => 'editar',
        'examen_id' => $_POST['examen_id'] ?? null,
        'historia_clinica_id' => $_POST['historia_clinica_id'] ?? null,
        'peso' => $_POST['peso'] ?? null,
        'talla' => $_POST['talla'] ?? null,
        'pulso' => $_POST['pulso'] ?? '',
        // El valor vacío se manejará como NULL en el DTO/Command
        'id_enfermero' => $_POST['id_enfermero'] ?? null, 
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    $objControl->ejecutarComando('editar', $data);
    
} else {
    // Si se accede sin acción POST válida
    header("Location: ./indexExamenEditar.php");
    exit();
}
?>