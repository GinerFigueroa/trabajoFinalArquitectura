<?php
// C:\...\agregarTratamiento\controlAgregarTratamiento.php
include_once('../../../../modelo/TratamientoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlAgregarTratamiento // PATRÓN: MEDIATOR / CONTROLLER
{
    private $objTratamiento;
    private $objMensaje;

    public function __construct()
    {
        $this->objTratamiento = new TratamientoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * PATRÓN: CHAIN OF RESPONSIBILITY para validaciones de negocio.
     * Acepta el DTO/Array.
     */
    private function validarRegistroChain(array $data)
    {
        // 1. Validar la existencia de la especialidad
        if (!$this->objTratamiento->especialidadExiste($data['idEspecialidad'])) {
            return "La especialidad seleccionada no es válida.";
        }

        // 2. Validar nombre único (No puede haber duplicados en la misma especialidad)
        // Nota: En registro, no pasamos el ID del tratamiento.
        if ($this->objTratamiento->validarNombreUnico($data['nombre'], $data['idEspecialidad'])) {
            return "Ya existe un tratamiento con el nombre '{$data['nombre']}' en esa especialidad.";
        }
        
        // 3. Validación de duración
        if (!is_numeric($data['duracion']) || $data['duracion'] <= 0) {
            return "La duración debe ser un número entero positivo.";
        }

        // 4. Validación del costo
        if (!is_numeric($data['costo']) || $data['costo'] < 0) {
            return "El costo debe ser un número positivo (o cero).";
        }
        
        // 5. Validación de estado (Aunque se asume 1, es buena práctica)
        if ($data['activo'] !== 1) {
             // Este caso solo debería ocurrir si el Builder se modifica, pero se mantiene como seguro
             return "Error interno en el estado del tratamiento.";
        }

        return true; // Pasa todas las validaciones
    }

    /**
     * Método principal para registrar un tratamiento (PATRÓN: COMMAND).
     * Acepta el DTO/Array.
     */
    public function registrarTratamiento(array $data)
    {
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarRegistroChain($data);

        if ($validacion !== true) {
            $this->objMensaje->mensajeSistemaShow($validacion, './indexAgregarTratamiento.php', 'systemOut', false);
            return;
        }
        
        // 2. Ejecución del COMMAND (Delegación al DAO con el DTO/Array)
        $resultado = $this->objTratamiento->registrarTratamiento($data);
        
        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Tratamiento registrado correctamente.', '../indexTipoTratamiento.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al registrar el tratamiento. Por favor, intente de nuevo.', './indexAgregarTratamiento.php', 'error');
        }
    }
}
?>