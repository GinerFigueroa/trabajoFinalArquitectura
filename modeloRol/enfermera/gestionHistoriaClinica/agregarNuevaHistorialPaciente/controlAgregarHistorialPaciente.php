<?php
include_once('../../../../modelo/HistoriaClinicaDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlAgregarHistorialPaciente
{
    private $objHistoriaDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objHistoriaDAO = new HistoriaClinicaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    public function agregarHistoria($datos)
    {
        $rutaRetorno = './indexAgregarHistorialPaciente.php';
        
        $idPaciente = (int)($datos['id_paciente'] ?? 0);
        $drTratanteId = (int)($datos['dr_tratante_id'] ?? 0);
        $fechaCreacion = date("Y-m-d"); 
        
        if (empty($idPaciente) || empty($drTratanteId)) {
            $this->objMensaje->mensajeSistemaShow("Falta seleccionar el Paciente o el ID del personal tratante.", $rutaRetorno, 'systemOut', false);
            return;
        }

        // 1. El DAO devuelve el ID recién insertado o FALSE si falla.
        $historiaClinicaId = $this->objHistoriaDAO->registrarHistoria(
            $idPaciente, 
            $drTratanteId, 
            $fechaCreacion
        );

        // 2. Comprueba si el resultado es un ID válido (mayor que 0).
        if ($historiaClinicaId > 0) {
            
            // 3. Redireccionar sin necesidad de llamar a getConnection()
            $this->objMensaje->mensajeSistemaShow(
                'Historia Clínica base creada correctamente. Proceda a completar la Anamnesis.', 
                // Ruta para continuar con la captura de información detallada
                '../indexHistoriaClinica.php?hc_id=' . $historiaClinicaId . '&pac_id=' . $idPaciente, 
                'success'
            );
        } else {
            // Error en la inserción (incluye Duplicate entry)
            $this->objMensaje->mensajeSistemaShow('Error al crear la Historia Clínica. El paciente seleccionado ya tiene una HC o hubo un fallo en la base de datos.', $rutaRetorno, 'error');
        }
    }
}
?>