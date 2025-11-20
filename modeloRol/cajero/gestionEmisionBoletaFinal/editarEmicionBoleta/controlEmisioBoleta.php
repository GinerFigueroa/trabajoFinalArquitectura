<?php
include_once('../../../../modelo/BoletaDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlEditarEmisionBoleta
{
    private $objBoletaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objBoletaDAO = new BoletaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function editarBoleta($idBoleta, $numeroBoleta, $tipo, $montoTotal, $metodoPago)
    {
        $urlRedireccion = "../indexEmisionBoletaFinal.php";
        $urlError = "./indexEditarEmisionBoleta.php?id={$idBoleta}";

        // Validación de monto
        if (!is_numeric($montoTotal) || $montoTotal <= 0) {
            $this->objMensaje->mensajeSistemaShow("El monto total debe ser un valor positivo.", $urlError, "error");
            return;
        }
        
        // Validación de ENUMs
        if (!in_array($tipo, BoletaAuxiliarDAO::obtenerTiposBoleta()) || !in_array($metodoPago, BoletaAuxiliarDAO::obtenerMetodosPago())) {
            $this->objMensaje->mensajeSistemaShow("Tipo de comprobante o método de pago no válido.", $urlError, "error");
            return;
        }

        $resultado = $this->objBoletaDAO->editarBoleta(
            $idBoleta, $numeroBoleta, $tipo, $montoTotal, $metodoPago
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Comprobante de pago actualizado correctamente.', 
                $urlRedireccion, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al actualizar el comprobante. Es posible que no se haya modificado ningún dato o hubo un error de BD.', 
                $urlError, 
                'error'
            );
        }
    }
}
?>