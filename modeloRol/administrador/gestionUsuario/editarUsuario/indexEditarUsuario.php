<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\editarUsuario\indexEditarUsuario.php
session_start();

include_once('./formEditarUsuario.php');

$idUsuario = $_GET['id'] ?? null;

$objForm = new formEditarUsuario();
$objForm->formEditarUsuarioShow($idUsuario);
?>
