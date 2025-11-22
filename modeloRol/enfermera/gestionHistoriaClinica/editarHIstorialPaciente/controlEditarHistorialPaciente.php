<?php
// C:\...\editarHistorialPaciente\controlEditarHistorialPaciente.php
include_once('../../../../modelo/HistoriaClinicaDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarHistorialPaciente
{
    private $objHistoriaDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objHistoriaDAO = new HistoriaClinicaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    public function editarHistoria($datos)
    {
        $historiaClinicaId = (int)($datos['historia_clinica_id'] ?? 0);
        $idPaciente = (int)($datos['id_paciente'] ?? 0);
        $drTratanteId = (int)($datos['dr_tratante_id'] ?? 0);
        $fechaCreacion = $datos['fecha_creacion'] ?? '';
        
        $urlRetorno = './indexEditarHistorialPaciente.php?id=' . $historiaClinicaId;

        // Validaciones
        if (empty($historiaClinicaId) || empty($idPaciente) || empty($drTratanteId) || empty($fechaCreacion)) {
            $this->objMensaje->mensajeSistemaShow("Todos los campos son obligatorios.", $urlRetorno, 'error');
            return;
        }

        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCreacion)) {
            $this->objMensaje->mensajeSistemaShow("Formato de fecha inválido.", $urlRetorno, 'error');
            return;
        }

        // Ejecutar la edición
        $resultado = $this->objHistoriaDAO->editarHistoria(
            $historiaClinicaId, 
            $idPaciente, 
            $drTratanteId, 
            $fechaCreacion
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Historia Clínica actualizada correctamente.', 
                '../indexHistoriaClinica.php', 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al actualizar la Historia Clínica. Verifique los datos.', 
                $urlRetorno, 
                'error'
            );
        }
    }
}
?>