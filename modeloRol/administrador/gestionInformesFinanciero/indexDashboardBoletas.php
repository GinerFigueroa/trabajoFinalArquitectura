<?php
// Archivo: indexDashboardBoletas.php

session_start();



include_once('./controlDashboardBoletas.php');

$objControl = new controlDashboardBoletas();
$objControl->mostrarDashboard();
?>