<?php
// C:\...\gestionTotalPacientes\agregarPaciente\formPacienteAgregar.php

include_once('../../../../shared/pantalla.php'); 
include_once('../../../../modelo/PacienteDAO.php'); 
 // Asegúrate de incluirlo

/**
 * Clase AuxiliarIterator (ITERATOR)
 */
if (!class_exists('AuxiliarIterator')) {
    class AuxiliarIterator implements Iterator {
        private $items; private $position = 0;
        public function __construct(array $items) { $this->items = $items; }
        public function rewind(): void { $this->position = 0; }
        public function current(): mixed { return $this->items[$this->position]; }
        public function key(): mixed { return $this->position; }
        public function next(): void { ++$this->position; }
        public function valid(): bool { return isset($this->items[$this->position]); }
    }
}

class formPacienteAgregar extends pantalla // TEMPLATE METHOD
{
    private $objEntidadPacienteDAO;

    public function __construct()
    {
        $this->objEntidadPacienteDAO = new EntidadPacienteDAO();
    }
    
    public function formPacienteAgregarShow()
    {
        $this->cabeceraShow('Registrar Datos Detallados del Paciente');

        // Obtener la lista de Usuarios que tienen el rol de paciente pero aún no tienen datos de paciente asociados.
        $listaUsuariosPacientes = $this->objEntidadPacienteDAO->obtenerTodosUsuariosPacientesSinAsignar();
        
        $usuarioIterator = new AuxiliarIterator($listaUsuariosPacientes); // Uso del ITERATOR
        
        // **NUEVA LÓGICA:** Determina si hay usuarios para habilitar el formulario.
        $hayUsuariosDisponibles = !empty($listaUsuariosPacientes);
        // Si no hay usuarios, deshabilitamos todos los campos del formulario.
        $disableForm = $hayUsuariosDisponibles ? '' : 'disabled'; 
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-person-plus-fill me-2"></i>Completar Expediente de Paciente</h4>
            <p class="mb-0">Solo se muestran usuarios con rol 'Paciente' sin datos personales asignados.</p>
        </div>
        <div class="card-body">
            
            <form action="./getPacienteAgregar.php" method="POST"> 
                
                <input type="hidden" name="action" value="registrar"> 

                <h5 class="mt-3 text-success">Selección de Usuario</h5>
                <hr>
                <div class="mb-3">
                    <label for="regUsuario" class="form-label">Usuario-Paciente a Asignar Datos:</label>
                    <select class="form-select" id="regUsuario" name="regUsuario" required <?php echo $disableForm; ?>>
                        <option value="">Seleccione Usuario</option>
                        <?php 
                        foreach ($usuarioIterator as $usuario) { 
                        ?>
                            <option value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">
                                <?php echo htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido_paterno'] . " (Usuario: " . $usuario['usuario_usuario'] . ")"); ?>
                            </option>
                        <?php 
                        }
                        if (!$usuarioIterator->valid() && empty($listaUsuariosPacientes)) {
                            echo '<option value="" disabled>Todos los usuarios pacientes ya tienen datos asignados.</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <h5 class="mt-4 text-success">Datos Personales Obligatorios</h5>
                <hr>
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="regDNI" class="form-label">DNI / Cédula:</label>
                        <input type="text" class="form-control" id="regDNI" name="regDNI" maxlength="15" required <?php echo $disableForm; ?>>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regFechaNacimiento" class="form-label">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="regFechaNacimiento" name="regFechaNacimiento" required <?php echo $disableForm; ?>>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regEdad" class="form-label">Edad (Calculada o Ingresada):</label>
                        <input type="number" class="form-control" id="regEdad" name="regEdad" min="0" max="150" required <?php echo $disableForm; ?>>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="regSexo" class="form-label">Sexo:</label>
                        <select class="form-select" id="regSexo" name="regSexo" required <?php echo $disableForm; ?>>
                            <option value="">Seleccione</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regEstadoCivil" class="form-label">Estado Civil:</label>
                        <select class="form-select" id="regEstadoCivil" name="regEstadoCivil" required <?php echo $disableForm; ?>>
                            <option value="">Seleccione</option>
                            <option value="Soltero">Soltero(a)</option>
                            <option value="Casado">Casado(a)</option>
                            <option value="Divorciado">Divorciado(a)</option>
                            <option value="Viudo">Viudo(a)</option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regOcupacion" class="form-label">Ocupación:</label>
                        <input type="text" class="form-control" id="regOcupacion" name="regOcupacion" required <?php echo $disableForm; ?>>
                    </div>
                </div>

                <h5 class="mt-4 text-success">Datos de Residencia</h5>
                <hr>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="regLugarNacimiento" class="form-label">Lugar de Nacimiento:</label>
                        <input type="text" class="form-control" id="regLugarNacimiento" name="regLugarNacimiento" required <?php echo $disableForm; ?>>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regDistrito" class="form-label">Distrito de Residencia:</label>
                        <input type="text" class="form-control" id="regDistrito" name="regDistrito" required <?php echo $disableForm; ?>>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="regDomicilio" class="form-label">Domicilio Completo:</label>
                    <input type="text" class="form-control" id="regDomicilio" name="regDomicilio" required <?php echo $disableForm; ?>>
                </div>

                <h5 class="mt-4 text-success">Datos del Apoderado (Contacto de Emergencia)</h5>
                <hr>
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="regNombreApoderado" class="form-label">Nombre(s) Apoderado:</label>
                        <input type="text" class="form-control" id="regNombreApoderado" name="regNombreApoderado" <?php echo $disableForm; ?>>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regApellidoPaternoApoderado" class="form-label">Apellido Paterno Apoderado:</label>
                        <input type="text" class="form-control" id="regApellidoPaternoApoderado" name="regApellidoPaternoApoderado" <?php echo $disableForm; ?>>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="regApellidoMaternoApoderado" class="form-label">Apellido Materno Apoderado:</label>
                        <input type="text" class="form-control" id="regApellidoMaternoApoderado" name="regApellidoMaternoApoderado" <?php echo $disableForm; ?>>
                    </div>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="regParentescoApoderado" class="form-label">Parentesco Apoderado:</label>
                    <input type="text" class="form-control" id="regParentescoApoderado" name="regParentescoApoderado" <?php echo $disableForm; ?>>
                </div>

                <?php 
                // Muestra la alerta si no hay usuarios disponibles.
                if (!$hayUsuariosDisponibles) { 
                ?>
                    <div class="alert alert-danger text-center mt-4">
                        ❌ **REGISTRO BLOQUEADO:** No hay usuarios disponibles con rol 'Paciente' sin datos personales asignados. <br> 
                        Por favor, cree un usuario con el rol 'Paciente' o verifique que no todos los pacientes ya tienen datos asociados.
                    </div>
                <?php 
                } 
                ?>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnRegistrar" class="btn btn-success" <?php echo $disableForm; ?>>Guardar Datos del Paciente</button>
                </div>
            </form>
            
            <div class="d-grid gap-2 mt-2">
                <a href="../indexTotalPaciente.php" class="btn btn-secondary">Volver a la Lista</a>
            </div>
        </div>
    </div>
</div>
<?php
        $this->pieShow();
    }
}
?>