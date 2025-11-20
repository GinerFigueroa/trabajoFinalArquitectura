<?php
include_once('../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlRecetaMedica
{
    private $objReceta;
    private $objMensaje;

    public function __construct()
    {
        $this->objReceta = new RecetaMedicaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarReceta($idReceta)
    {
        if (empty($idReceta) || !is_numeric($idReceta)) {
            $this->objMensaje->mensajeSistemaShow("ID de receta no válido.", "./indexRecetaMedica.php", "systemOut", false);
            return;
        }

        $resultado = $this->objReceta->eliminarReceta($idReceta);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Receta médica eliminada correctamente.", "./indexRecetaMedica.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la receta médica.", "./indexRecetaMedica.php", "error");
        }
    }
}
?>