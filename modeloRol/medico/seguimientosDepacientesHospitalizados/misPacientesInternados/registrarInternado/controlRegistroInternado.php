<?php
include_once('../../../../../modelo/InternadoDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlRegistroInternado
{
    private $objInternado;
    private $objAuxiliar;
    private $objMensaje;

    public function __construct()
    {
        $this->objInternado = new InternadoDAO();
        $this->objAuxiliar = new InternadoAuxiliarDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function registrarInternado($idPaciente, $idHabitacion, $idMedico, $fechaIngreso, $diagnostico, $observaciones)
    {
        $urlRetorno = './indexRegistroInternado.php';

        // 1. Validaciones básicas
        if (empty($idPaciente) || empty($idHabitacion) || empty($idMedico) || empty($fechaIngreso) || empty($diagnostico)) {
            $this->objMensaje->mensajeSistemaShow("Todos los campos marcados con (*) son obligatorios.", $urlRetorno, "error");
            return;
        }

        // 2. Validar que los IDs sean numéricos
        if (!is_numeric($idPaciente) || !is_numeric($idHabitacion) || !is_numeric($idMedico)) {
            $this->objMensaje->mensajeSistemaShow("IDs de paciente, habitación o médico no válidos.", $urlRetorno, "error");
            return;
        }

        // 3. Validar fecha (no puede ser futura)
        $fechaIngresoDateTime = new DateTime($fechaIngreso);
        $fechaActual = new DateTime();
        
        if ($fechaIngresoDateTime > $fechaActual) {
            $this->objMensaje->mensajeSistemaShow("La fecha de ingreso no puede ser futura.", $urlRetorno, "error");
            return;
        }

        // 4. Validar existencia de entidades
        if (!$this->objAuxiliar->pacienteExiste($idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("El paciente seleccionado no existe o no está activo.", $urlRetorno, "error");
            return;
        }

        if (!$this->objAuxiliar->medicoExiste($idMedico)) {
            $this->objMensaje->mensajeSistemaShow("El médico seleccionado no existe o no está activo.", $urlRetorno, "error");
            return;
        }

        // 5. Validar que la habitación esté disponible
        if (!$this->objInternado->habitacionDisponible($idHabitacion)) {
            $this->objMensaje->mensajeSistemaShow("La habitación seleccionada no está disponible.", $urlRetorno, "error");
            return;
        }

        // 6. Validar que el paciente no esté ya internado
        if ($this->objInternado->pacienteYaInternado($idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("El paciente ya se encuentra internado actualmente.", $urlRetorno, "error");
            return;
        }

        // 7. Formatear fecha para MySQL
        $fechaIngresoFormateada = $fechaIngresoDateTime->format('Y-m-d H:i:s');

        // 8. Ejecutar el registro (transaccional)
        $resultado = $this->objInternado->registrarInternado(
            $idPaciente,
            $idHabitacion,
            $idMedico,
            $fechaIngresoFormateada,
            trim($diagnostico),
            trim($observaciones)
        );

        // 9. Manejar resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Internado registrado correctamente. El paciente ha sido asignado a la habitación.",
                "../indexGestionInternados.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al registrar el internado. Por favor, intente nuevamente.",
                $urlRetorno,
                "error"
            );
        }
    }
}
?>