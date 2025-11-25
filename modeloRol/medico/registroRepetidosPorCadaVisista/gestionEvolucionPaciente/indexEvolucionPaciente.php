<?php
// Directorio: /vista/evolucion/indexEvolucionPaciente.php

session_start();
// Atributo: $obj (Instancia de la Vista)
include_once('./formEvolucionPaciente.php');

$obj = new formEvolucionPaciente();
// Método: formEvolucionPacienteShow (Muestra la Vista)
$obj->formEvolucionPacienteShow();
?>