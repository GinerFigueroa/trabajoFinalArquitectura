<?php
// Archivo: indexRegistroUsuario.php

/**
 * PUNTO DE ENTRADA EXCLUSIVO PARA REGISTRO DE PACIENTES
 * No requiere sesión activa - acceso público
 */

include_once('./formRegistroUsuario.php'); 
$obj = new formRegistroUsuario();
$obj->formRegistroUsuarioShow();
?>