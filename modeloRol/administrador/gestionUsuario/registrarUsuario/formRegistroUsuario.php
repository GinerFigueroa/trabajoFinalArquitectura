<?php

include_once('../../../../shared/pantalla.php'); // TEMPLATE METHOD (Heredado)
include_once('../../../../modelo/RolDAO.php'); 

/**
 * Clase RolIterator (Emulación de ITERATOR)
 * Permite recorrer la lista de roles de forma controlada.
 */
if (!class_exists('RolIterator')) {
    class RolIterator implements Iterator {
        private $roles; 
        private $position = 0;
        
        public function __construct(array $roles) { $this->roles = $roles; }
        public function rewind(): void { $this->position = 0; }
        public function current(): mixed { return $this->roles[$this->position]; }
        public function key(): mixed { return $this->position; }
        public function next(): void { ++$this->position; }
        public function valid(): bool { return isset($this->roles[$this->position]); }
    }
}

class formRegistroUsuario extends pantalla // TEMPLATE METHOD
{
    private $estadoFormulario = 'inicial'; // Emulación de STATE

    /**
     * Emulación del patrón STATE: Cambia el estado interno del formulario.
     */
    private function setEstadoFormulario($estado) {
        $this->estadoFormulario = $estado;
    }

    public function formRegistroUsuarioShow()
    {
        $this->setEstadoFormulario('cargando');
        $this->cabeceraShow('Registrar Usuario');

        // Delegación: Consulta al Modelo/DAO para obtener los datos necesarios
        $objRol = new RolDAO();
        $listaRoles = $objRol->obtenerTodosRoles();
        
        $this->setEstadoFormulario('mostrando');
        $rolesIterator = new RolIterator($listaRoles); // Uso del ITERATOR
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario (Estado: **<?php echo $this->estadoFormulario; ?>**)</h4>
        </div>
        <div class="card-body">
            <form action="./getRegistrarUsuario.php" method="POST"> 
                
                <input type="hidden" name="action" value="registrar"> 

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="regUsuario" class="form-label">Usuario:</label>
                        <input type="text" class="form-control" id="regUsuario" name="regUsuario" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regEmail" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="regEmail" name="regEmail" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regNombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="regNombre" name="regNombre" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regApellidoPaterno" class="form-label">Apellido Paterno:</label>
                        <input type="text" class="form-control" id="regApellidoPaterno" name="regApellidoPaterno" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regApellidoMaterno" class="form-label">Apellido Materno:</label>
                        <input type="text" class="form-control" id="regApellidoMaterno" name="regApellidoMaterno">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regTelefono" class="form-label">Teléfono:</label>
                        <input type="tel" class="form-control" id="regTelefono" name="regTelefono" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="regClave" class="form-label">Clave:</label>
                    <input type="password" class="form-control" id="regClave" name="regClave" required>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="regRol" class="form-label">Rol:</label>
                        <select class="form-select" id="regRol" name="regRol" required>
                            <?php 
                            // Uso del ITERATOR para generar las opciones
                            foreach ($rolesIterator as $rol) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($rol['id_rol']); ?>">
                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                </option>
                            <?php 
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="regActivo" class="form-label">Estado:</label>
                        <select class="form-select" id="regActivo" name="regActivo" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="btnRegistrar" class="btn btn-success">Registrar</button>
                    <a href="../formGestionUsuario.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
        $this->pieShow();
    }
}
?>