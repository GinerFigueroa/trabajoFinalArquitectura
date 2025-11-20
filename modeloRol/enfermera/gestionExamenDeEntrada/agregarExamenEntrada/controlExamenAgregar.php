<?php
include_once("../../../../modelo/ExamenClinicoDAO.php");
include_once('../../../../shared/mensajeSistema.php');

class controlExamenAgregar
{
    private $objExamenDAO;
    // Eliminado: private $objAuxiliarDAO; // <- ¡Esta línea causaba el error!
    private $objMensaje;

    public function __construct()
    {
        $this->objExamenDAO = new ExamenClinicoDAO(); 
        // Eliminado: $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); // Se asume que estos métodos fueron movidos a ExamenClinicoDAO
        $this->objMensaje = new mensajeSistema();
    }

    public function registrarExamen($historiaClinicaId, $peso, $talla, $pulso, $idEnfermero)
    {
        $rutaRetorno = './indexExamenAgregar.php';
        $pulso = trim($pulso);
        
        // --- 1. Validaciones de Campo Vacío (Obligatorias) ---
        if (empty($historiaClinicaId) || empty($peso) || empty($talla) || empty($pulso)) {
            $this->objMensaje->mensajeSistemaShow("Faltan campos obligatorios (Paciente, Peso, Talla y Pulso).", $rutaRetorno, 'systemOut', false);
            return;
        }

        // --- 2. Validaciones de Formato y Límite ---
        if (!is_numeric($peso) || $peso <= 0 || $peso > 500) {
            $this->objMensaje->mensajeSistemaShow("El campo Peso debe ser un valor numérico positivo (máx 500).", $rutaRetorno, 'systemOut', false);
            return;
        }
        if (!is_numeric($talla) || $talla <= 0 || $talla > 3.0) {
            $this->objMensaje->mensajeSistemaShow("El campo Talla debe ser un valor numérico positivo (máx 3.0).", $rutaRetorno, 'systemOut', false);
            return;
        }
        if (strlen($pulso) > 20) {
            $this->objMensaje->mensajeSistemaShow("El campo Pulso no debe exceder los 20 caracteres.", $rutaRetorno, 'systemOut', false);
            return;
        }
        
        // --- 3. Validaciones de Existencia de Entidades ---
        
        // a) Validar que el Paciente/Historia Clínica exista (Llamando al método integrado en ExamenClinicoDAO)
        if (!$this->objExamenDAO->obtenerNombrePacientePorHistoriaClinica($historiaClinicaId)) {
            $this->objMensaje->mensajeSistemaShow("El ID de Historia Clínica seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
            return;
        }
        
        // b) Validar ID de Enfermera si no está vacío (Llamando al método integrado en ExamenClinicoDAO)
        $idEnfermero = empty($idEnfermero) ? NULL : (int)$idEnfermero; 
        
        // Se asume que ExamenClinicoDAO tiene el método obtenerNombrePersonalPorIdUsuario() integrado
        if ($idEnfermero !== NULL && !$this->objExamenDAO->obtenerNombrePersonalPorIdUsuario($idEnfermero)) { 
            $this->objMensaje->mensajeSistemaShow("El ID de Enfermera/o seleccionado no es válido o no existe.", $rutaRetorno, 'systemOut', false);
            return;
        }

        // --- 4. Ejecución de la Acción ---
        $resultado = $this->objExamenDAO->registrarExamen(
            $historiaClinicaId, 
            $peso, 
            $talla, 
            $pulso, 
            $idEnfermero
        );

        // --- 5. Manejo de Respuesta ---
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Examen Clínico de Entrada registrado correctamente.', '../indexExamenEntrada.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al registrar el examen. Fallo en la base de datos.', $rutaRetorno, 'error');
        }
    }
}
?>