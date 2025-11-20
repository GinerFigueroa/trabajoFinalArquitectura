<?php
include_once('../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlExamenClinico
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarOrden($idOrden)
    {
        $resultado = $this->objDAO->eliminarOrden($idOrden);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de examen eliminada correctamente.', './indexOrdenExamenClinico.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al eliminar la orden de examen.', './indexOrdenExamenClinico.php', 'error');
        }
    }
}
?>