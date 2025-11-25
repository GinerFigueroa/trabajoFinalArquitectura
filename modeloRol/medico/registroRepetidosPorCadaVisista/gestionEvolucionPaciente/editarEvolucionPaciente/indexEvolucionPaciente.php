<?php
// Directorio: /vista/evolucion/editarEvolucionPaciente/indexEditarEvolucionPaciente.php

session_start();

// Atributo: $objForm (Instancia de la Vista)
include_once('./formEditarEvolucionPaciente.php');

$objForm = new formEditarEvolucionPaciente();
// Método: formEditarEvolucionPacienteShow (Muestra la Vista)
$objForm->formEditarEvolucionPacienteShow();
?>