<?php
// Archivo: getEditarPaciente.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarPaciente.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnEditar'])) {
    
    // PATRÓN BUILDER (Construcción del DTO/Array de Datos)
    // Se asegura de que todos los campos esperados estén presentes y tipados.
    $datos = [
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'fechaNacimiento' => $_POST['fechaNacimiento'] ?? null,
        'lugarNacimiento' => $_POST['lugarNacimiento'] ?? null,
        'ocupacion' => $_POST['ocupacion'] ?? null,
        'dni' => $_POST['dni'] ?? null,
        'domicilio' => $_POST['domicilio'] ?? null,
        'distrito' => $_POST['distrito'] ?? null,
        'edad' => empty($_POST['edad']) ? null : (int)$_POST['edad'], 
        'sexo' => $_POST['sexo'] ?? null,
        'estadoCivil' => $_POST['estadoCivil'] ?? null,
        'nombreApoderado' => $_POST['nombreApoderado'] ?? null,
        'apellidoPaternoApoderado' => $_POST['apellidoPaternoApoderado'] ?? null,
        'apellidoMaternoApoderado' => $_POST['apellidoMaternoApoderado'] ?? null,
        'parentescoApoderado' => $_POST['parentescoApoderado'] ?? null
    ];

    // DISPATCHER: Crea el Command y lo ejecuta
    $objControl = new controlEditarPaciente();
    $objControl->editarPaciente($datos); // Ejecuta el Command
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexTotalPaciente.php', 'systemOut', false);
}
?>