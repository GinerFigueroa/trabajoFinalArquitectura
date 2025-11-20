<?php
include_once("../../../shared/pantalla.php"); 
include_once("../../../modelo/UsuarioDAO.php"); 

/**
 * Clase UsuarioIterator (ITERATOR)
 */
class UsuarioIterator implements Iterator {
    private $usuarios;
    private $position = 0;
    public function __construct(array $usuarios) { $this->usuarios = $usuarios; }
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->usuarios[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->usuarios[$this->position]); }
}

class formGestionUsuario extends pantalla // TEMPLATE METHOD
{
    private $estadoFormulario = 'inicial'; // STATE

    private function setEstadoFormulario($estado) { $this->estadoFormulario = $estado; }

    // VISITOR: Aplica lógica de presentación de estado
    private function visitarEstadoUsuario($usuario) {
        $activo = $usuario['activo'] == 1;
        $color = $activo ? 'success' : 'danger';
        $texto = $activo ? 'Activo' : 'Inactivo';
        $esEliminable = $activo;
        $esReactivable = !$activo; // NUEVO: indica si se puede reactivar
        
        return [
            'color' => $color,
            'texto' => $texto,
            'esEliminable' => $esEliminable,
            'esReactivable' => $esReactivable // NUEVO
        ];
    }

    public function formGestionUsuarioShow()
    {
        $this->setEstadoFormulario('cargando');
        $this->cabeceraShow("Gestión de Usuarios");

        $objUsuarioDAO = new UsuarioDAO(); 
        $listaUsuarios = $objUsuarioDAO->obtenerTodosUsuarios();
        
        $this->setEstadoFormulario('listando');
        $usuarioIterator = new UsuarioIterator($listaUsuarios); // ITERATOR
?>

<div class="row mb-3">
    <!-- Botón Registrar Usuario -->
    <div class="col-md-4 text-start">
        <a href="./registrarUsuario/indexRegitroUsuario.php" class="btn btn-success w-100">
            <i class="bi bi-person-add me-2"></i>Registrar Usuario
        </a>
    </div>

    <!-- Botón Informes -->
    <div class="col-md-4 text-center">
        <a href="../gestionInformesFinanciero/indexDashboardBoletas.php" class="btn btn-dark w-100">
            <i class="bi bi-bar-chart-line me-2"></i>Informes
        </a>
    </div>

    <!-- Botón Tipo de Tratamiento -->
    <div class="col-md-4 text-end">
        <a href="../gestionTipoDeTratamientoCosto/indexTipoTratamiento.php" class="btn btn-primary w-100">
            <i class="bi bi-arrow-right-circle me-2"></i>Tipo de Tratamiento
        </a>
    </div>
</div>


            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th><th>Usuario</th><th>Nombre Completo</th>
                            <th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($usuarioIterator->valid()) {
                            foreach ($usuarioIterator as $usuario) { 
                                $estadoVisitado = $this->visitarEstadoUsuario($usuario); // VISITOR
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['usuario_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido_paterno'] . " " . $usuario['apellido_materno']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol_nombre'] ?? 'N/A'); ?></td> 
                                    <td>
                                        <span class="badge rounded-pill bg-<?php echo $estadoVisitado['color']; ?>">
                                            <?php echo $estadoVisitado['texto']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="./editarUsuario/indexEditarUsuario.php?id=<?php echo htmlspecialchars($usuario['id_usuario']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php if ($estadoVisitado['esEliminable']) { ?>
                                            <button class="btn btn-sm btn-danger" title="Eliminar/Desactivar" onclick="confirmarEliminar(<?php echo htmlspecialchars($usuario['id_usuario']); ?>)">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        <?php } ?>
                                        <?php if ($estadoVisitado['esReactivable']) { ?>
                                            <button class="btn btn-sm btn-success" title="Reactivar" onclick="confirmarReactivar(<?php echo htmlspecialchars($usuario['id_usuario']); ?>)">
                                                <i class="bi bi-person-check-fill"></i>
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr><td colspan="7" class="text-center">No hay usuarios registrados.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        if (confirm('¿Está seguro de que desea ELIMINAR este usuario?\n\n• Si NO tiene relaciones: se ELIMINARÁ completamente\n• Si tiene relaciones: se DESACTIVARÁ')) {
            window.location.href = `./getGestionUsuario.php?action=eliminar&id=${id}`;
        }
    }

    function confirmarReactivar(id) {
        if (confirm('¿Está seguro de que desea REACTIVAR este usuario?\n\nEl usuario podrá acceder al sistema nuevamente.')) {
            window.location.href = `./getGestionUsuario.php?action=reactivar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}