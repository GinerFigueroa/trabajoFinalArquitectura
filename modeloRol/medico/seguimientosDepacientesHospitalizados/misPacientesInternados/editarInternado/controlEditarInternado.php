<?php
include_once('../../../../../modelo/InternadoDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlEditarInternado
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

    public function editarInternado($idInternado, $idHabitacion, $idMedico, $fechaAlta, $diagnostico, $observaciones, $estado, $idHabitacionAnterior)
    {
        $urlRetorno = './indexEditarInternado.php?id=' . $idInternado;

        try {
            // 1. Validaciones básicas
            if (empty($idInternado) || empty($idHabitacion) || empty($idMedico) || empty($diagnostico) || empty($estado)) {
                throw new Exception("Todos los campos obligatorios deben estar completos.");
            }

            // 2. Validar que los IDs sean numéricos
            if (!is_numeric($idInternado) || !is_numeric($idHabitacion) || !is_numeric($idMedico) || !is_numeric($idHabitacionAnterior)) {
                throw new Exception("IDs de internado, habitación o médico no válidos.");
            }

            // 3. Verificar que el internado existe y está activo
            $internadoActual = $this->objInternado->obtenerInternadoPorId($idInternado);
            if (!$internadoActual) {
                throw new Exception("El internado no existe.");
            }

            if ($internadoActual['estado'] != 'Activo') {
                throw new Exception("Solo se pueden editar internados con estado 'Activo'.");
            }

            // 4. Validar existencia de entidades
            if (!$this->objAuxiliar->medicoExiste($idMedico)) {
                throw new Exception("El médico seleccionado no existe o no está activo.");
            }

            // 5. Validar habitación (si se cambió)
            if ($idHabitacion != $idHabitacionAnterior) {
                if (!$this->objInternado->habitacionDisponible($idHabitacion)) {
                    throw new Exception("La habitación seleccionada ya no está disponible. Por favor, seleccione otra habitación.");
                }
            }

            // 6. Validar fecha de alta si el estado no es Activo
            $fechaAltaFormateada = null;
            if ($estado != 'Activo' && !empty($fechaAlta)) {
                $fechaAltaDateTime = new DateTime($fechaAlta);
                $fechaIngresoDateTime = new DateTime($internadoActual['fecha_ingreso']);
                $fechaActual = new DateTime();

                // Validar que la fecha de alta no sea futura
                if ($fechaAltaDateTime > $fechaActual) {
                    throw new Exception("La fecha de alta no puede ser futura.");
                }

                // Validar que la fecha de alta no sea anterior a la fecha de ingreso
                if ($fechaAltaDateTime < $fechaIngresoDateTime) {
                    throw new Exception("La fecha de alta no puede ser anterior a la fecha de ingreso.");
                }

                $fechaAltaFormateada = $fechaAltaDateTime->format('Y-m-d H:i:s');
            }

            // 7. Si el estado cambia a no Activo y no hay fecha de alta, usar fecha actual
            if ($estado != 'Activo' && empty($fechaAlta)) {
                $fechaAltaFormateada = date('Y-m-d H:i:s');
            }

            // 8. Ejecutar la edición (transaccional)
            $resultado = $this->objInternado->editarInternado(
                $idInternado,
                $idHabitacion,
                $idMedico,
                $fechaAltaFormateada,
                trim($diagnostico),
                trim($observaciones),
                $estado,
                $idHabitacionAnterior
            );

            // 9. Manejar resultado
            if ($resultado) {
                $mensaje = "✅ Internado actualizado correctamente.";
                
                // Mensajes adicionales según los cambios
                if ($idHabitacion != $idHabitacionAnterior) {
                    $mensaje .= " La habitación ha sido cambiada.";
                }
                
                if ($estado != 'Activo') {
                    $mensaje .= " El paciente ha sido dado de alta y la habitación liberada.";
                }

                $this->objMensaje->mensajeSistemaShow(
                    $mensaje,
                    "../indexGestionInternados.php",
                    "success"
                );
            } else {
                throw new Exception("Error al actualizar el internado en la base de datos. Por favor, intente nuevamente.");
            }

        } catch (Exception $e) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ " . $e->getMessage(),
                $urlRetorno,
                "error"
            );
        }
    }
}
?>