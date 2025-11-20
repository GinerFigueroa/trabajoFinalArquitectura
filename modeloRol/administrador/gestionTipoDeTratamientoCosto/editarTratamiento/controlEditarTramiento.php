<?php
// C:\...\editarTratamiento\controlEditarTratamiento.php
include_once('../../../../modelo/TratamientoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlEditarTratamiento // PATRÓN: MEDIATOR / CONTROLLER
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
    private function validarEdicionChain(array $data)
    {
        // 1. Validar la existencia de la especialidad (Delega al DAO)
        if (!$this->objTratamiento->especialidadExiste($data['idEspecialidad'])) {
            return "La especialidad seleccionada no es válida.";
        }

        // 2. Validar nombre único (Excluyendo el ID actual)
        if ($this->objTratamiento->validarNombreUnico($data['nombre'], $data['idEspecialidad'], $data['idTratamiento'])) {
            return "Ya existe otro tratamiento con el nombre '{$data['nombre']}' en esa especialidad.";
        }
        
        // 3. Validación de duración
        if (!is_numeric($data['duracion']) || $data['duracion'] <= 0) {
            return "La duración debe ser un número entero positivo.";
        }

        // 4. Validación del costo
        if (!is_numeric($data['costo']) || $data['costo'] < 0) {
            return "El costo debe ser un número positivo.";
        }
        
        // 5. Validación de estado
        if ($data['activo'] !== '0' && $data['activo'] !== '1') {
            return "El estado del tratamiento no es válido.";
        }

        return true; // Pasa todas las validaciones
    }

    /**
     * Método principal para editar un tratamiento (PATRÓN: COMMAND).
     * Acepta el DTO/Array.
     */
    public function editarTratamiento(array $data)
    {
        $idTratamiento = $data['idTratamiento']; 
        
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarEdicionChain($data);

        if ($validacion !== true) {
            $this->objMensaje->mensajeSistemaShow($validacion, './indexEditarTratamiento.php?id=' . $idTratamiento, 'systemOut', false);
            return;
        }
        
        // 2. Ejecución del COMMAND (Delegación al DAO con el DTO/Array)
        $resultado = $this->objTratamiento->editarTratamiento($data);
        
        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Tratamiento editado correctamente.', '../indexTipoTratamiento.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar el tratamiento. Por favor, intente de nuevo.', './indexEditarTratamiento.php?id=' . $idTratamiento, 'error');
        }
    }
}
?>