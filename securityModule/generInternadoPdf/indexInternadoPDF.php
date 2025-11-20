<?php
session_start();
include_once('./formInternadoPDF.php');
$obj = new formInternadoPDF();
$obj->formInternadoPDFShow();
?>