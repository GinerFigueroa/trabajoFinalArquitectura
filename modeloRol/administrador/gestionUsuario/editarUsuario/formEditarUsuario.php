<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\editarUsuario\formEditarUsuario.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/UsuarioDAO.php');
include_once('../../../../modelo/RolDAO.php');

/**
 * Clase RolIterator (Emulación de ITERATOR)
 */
if (!class_exists('RolIterator')) {
    class RolIterator implements Iterator {
        private $roles; private $position = 0;
        public function __construct(array $roles) { $this->roles = $roles; }
        public function rewind(): void { $this->position = 0; }
        public function current(): mixed { return $this->roles[$this->position]; }
        public function key(): mixed { return $this->position; }
        public function next(): void { ++$this->position; }
        public function valid(): bool { return isset($this->roles[$this->position]); }
    }
}

class formEditarUsuario extends pantalla // TEMPLATE METHOD
{
    private $estadoFormulario = 'inicial'; // Emulación de STATE

    private function setEstadoFormulario($estado) { $this->estadoFormulario = $estado; }

    public function formEditarUsuarioShow($idUsuario = null)
    {
        $this->setEstadoFormulario('cargando');
        $this->cabeceraShow('Editar Usuario');

        if (is_null($idUsuario)) {
            $idUsuario = isset($_GET['id']) ? $_GET['id'] : null;
        }

        if (!$idUsuario) {
            $this->setEstadoFormulario('error_id');
            echo '<div class="alert alert-danger" role="alert">ID de usuario no proporcionado. (Estado: ' . $this->estadoFormulario . ')</div>';
            $this->pieShow(); return;
        }

        $objUsuario = new UsuarioDAO();
        $objRol = new RolDAO();

        $usuario = $objUsuario->obtenerUsuarioPorId($idUsuario);
        $listaRoles = $objRol->obtenerTodosRoles();

        if (!$usuario) {
            $this->setEstadoFormulario('error_not_found');
            echo '<div class="alert alert-danger" role="alert">Usuario no encontrado. (Estado: ' . $this->estadoFormulario . ')</div>';
            $this->pieShow(); return;
        }
        
        $this->setEstadoFormulario('mostrando');
        $rolesIterator = new RolIterator($listaRoles); // Uso de ITERATOR
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-person-fill-gear me-2"></i>Editar Usuario (Estado: <?php echo $this->estadoFormulario; ?>)</h4>
        </div>
        <div class="card-body">
            <form action="./getEditarUsuario.php" method="POST">
                <input type="hidden" name="idUsuario" value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">
                <input type="hidden" name="action" value="editar"> 
                
                <div class="mb-3">
                    <label for="editUsuario" class="form-label">Usuario:</label>
                    <input type="text" class="form-control" id="editUsuario" name="editUsuario" value="<?php echo htmlspecialchars($usuario['usuario_usuario']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editNombre" class="form-label">Nombre:</label>
                    <input type="text" class="form-control" id="editNombre" name="editNombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editApellidoPaterno" class="form-label">Apellido Paterno:</label>
                    <input type="text" class="form-control" id="editApellidoPaterno" name="editApellidoPaterno" value="<?php echo htmlspecialchars($usuario['apellido_paterno']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editApellidoMaterno" class="form-label">Apellido Materno:</label>
                    <input type="text" class="form-control" id="editApellidoMaterno" name="editApellidoMaterno" value="<?php echo htmlspecialchars($usuario['apellido_materno'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="editEmail" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="editEmail" name="editEmail" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editTelefono" class="form-label">Teléfono:</label>
                    <input type="tel" class="form-control" id="editTelefono" name="editTelefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="editClave" class="form-label">Nueva Clave (dejar en blanco para no cambiar):</label>
                    <input type="password" class="form-control" id="editClave" name="editClave">
                </div>
                <div class="mb-3">
                    <label for="editRol" class="form-label">Rol:</label>
                    <select class="form-select" id="editRol" name="editRol" required>
                        <?php 
                        foreach ($rolesIterator as $rol) { 
                        ?>
                            <option value="<?php echo htmlspecialchars($rol['id_rol']); ?>" <?php echo ($usuario['id_rol'] == $rol['id_rol']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rol['nombre']); ?>
                            </option>
                        <?php 
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editActivo" class="form-label">Estado:</label>
                    <select class="form-select" id="editActivo" name="editActivo" required>
                        <option value="1" <?php echo $usuario['activo'] == 1 ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo $usuario['activo'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="btnEditar" class="btn btn-warning">Guardar Cambios</button>
                    <a href="../indexGestionUsuario.php" class="btn btn-secondary">Cancelar</a> 
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