<?php
// FILE: indexCitas.php

session_start();
include_once('./formConsultarCitas.php'); // Incluye la vista actualizada
$obj = new formConsultarCitas(); // Instancia de la Vista (Template Method)
$obj->formConsultarCitasShow(); // Ejecuta el Template Method
?>