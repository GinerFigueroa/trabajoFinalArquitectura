<?php
include_once('../../../../modelo/InternadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlGestionInternados
{
    private $objInternado;
    private $objMensaje;

    public function __construct()
    {
        $this->objInternado = new InternadoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function darAltaInternado($idInternado)
    {
        if (empty($idInternado) || !is_numeric($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("ID de internado no válido.", "./indexGestionInternados.php", "error");
            return;
        }

        // Obtener datos actuales del internado
        $internado = $this->objInternado->obtenerInternadoPorId($idInternado);
        
        if (!$internado) {
            $this->objMensaje->mensajeSistemaShow("Internado no encontrado.", "./indexGestionInternados.php", "error");
            return;
        }

        if ($internado['estado'] != 'Activo') {
            $this->objMensaje->mensajeSistemaShow("Solo se puede dar de alta a pacientes con estado 'Activo'.", "./indexGestionInternados.php", "error");
            return;
        }

        // Dar de alta (actualizar estado y fecha_alta)
        $fechaAlta = date('Y-m-d H:i:s');
        $resultado = $this->objInternado->editarInternado(
            $idInternado,
            $internado['id_habitacion'], // Mantener misma habitación
            $internado['id_medico'],
            $fechaAlta,
            $internado['diagnostico_ingreso'],
            $internado['observaciones'],
            'Alta', // Nuevo estado
            $internado['id_habitacion'] // Habitación anterior (misma)
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Paciente dado de alta correctamente. La habitación ha sido liberada.", "./indexGestionInternados.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al dar de alta al paciente.", "./indexGestionInternados.php", "error");
        }
    }
}
?>