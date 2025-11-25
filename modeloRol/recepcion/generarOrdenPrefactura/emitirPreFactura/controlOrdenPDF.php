<?php
// FILE: controlOrdenPDF.php

include_once('../../../../modelo/OrdenPagoDAO.php'); // Modelo
include_once('../../../../shared/mensajeSistema.php');
include_once('./formOdenPDF.php'); // Vista (Template Method)

/**
 * PATRÓN: MEDIATOR (Clase Controladora)
 * Centraliza el flujo entre la obtención de datos (DAO) y la presentación (formOdenPDF).
 */
class controlOrdenPDF
{
    // ATRIBUTOS
    private $objOrden;    // Receptor del Modelo
    private $objMensaje;
    private $objFormPDF;  // Vista/Template Method

    // MÉTODO (Constructor)
    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formOdenPDF(); // Instancia del Template Method
    }

    /**
     * MÉTODO (Punto de entrada/Mediator)
     */
    public function generarPDF()
    {
        $idOrden = $_GET['id'] ?? null;
        
        // 1. Validar la entrada
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de Orden de Pago no proporcionado o no válido.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        // 2. Obtener datos del Modelo
        $orden = $this->objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            $this->objMensaje->mensajeSistemaShow("Orden de Pago no encontrada.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        // 3. Delegar la generación al Template Method (Vista)
        // El Mediator pasa los datos brutos a la Vista y le pide ejecutar su algoritmo de presentación.
        $this->objFormPDF->generarPDFShow($orden);
    }
}
?>