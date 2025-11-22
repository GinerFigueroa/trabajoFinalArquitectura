<?php
// Archivo: controlEditarFacturacionInternado.php

include_once('../../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlEditarFacturacionInternado
{
    private $objFacturaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objFacturaDAO = new FacturacionInternadoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function editarFacturaInternado(
        $idFactura, $idInternado, $fechaEmision, $diasInternado, $costoHabitacion, 
        $costoTratamientos, $costoMedicamentos, $costoOtros, $total, $estado)
    {
        $urlRedireccion = "../indexFacturacionInternadoPDF.php";
        $urlError = "./indexEditarFacturacionInternado.php?id={$idFactura}";

        // Validación de datos numéricos y estado
        if (!is_numeric($total) || $total <= 0) {
            $this->objMensaje->mensajeSistemaShow("El monto total debe ser un valor positivo.", $urlError, "error");
            return;
        }
        if (!in_array($estado, FacturacionInternadoAuxiliarDAO::obtenerEstadosFactura())) {
            $this->objMensaje->mensajeSistemaShow("Estado de factura no válido.", $urlError, "error");
            return;
        }

        $resultado = $this->objFacturaDAO->editarFacturaInternado(
            $idFactura, $idInternado, $fechaEmision, $diasInternado, $costoHabitacion, 
            $costoTratamientos, $costoMedicamentos, $costoOtros, $total, $estado
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Factura de Internado actualizada correctamente.', 
                $urlRedireccion, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al actualizar la factura. Es posible que no se haya modificado ningún dato o hubo un error de BD.', 
                $urlError, 
                'error'
            );
        }
    }
}
?>