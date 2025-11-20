<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\formGestionUsuario.php
include_once("../../../shared/pantalla.php"); 
include_once("../../../modelo/UsuarioDAO.php"); 

/**
 * Clase UsuarioIterator (Emulación de ITERATOR)
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
    private $estadoFormulario = 'inicial'; // Emulación de STATE

    private function setEstadoFormulario($estado) { $this->estadoFormulario = $estado; }

    // Emulación del patrón VISITOR: Aplica lógica de presentación de estado
    private function acceptVisitor($usuario) {
        $activo = $usuario['activo'] == 1;
        $color = $activo ? 'success' : 'danger';
        $texto = $activo ? 'Activo' : 'Inactivo';
        return ['color' => $color, 'texto' => $texto];
    }

    public function formGestionUsuarioShow()
    {
        $this->setEstadoFormulario('cargando');
        $this->cabeceraShow("Gestión de Usuarios");

        $objUsuarioDAO = new UsuarioDAO(); 
        $listaUsuarios = $objUsuarioDAO->obtenerTodosUsuarios();
        
        $this->setEstadoFormulario('listando');
        $usuarioIterator = new UsuarioIterator($listaUsuarios); // Uso del ITERATOR
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-person-fill-gear me-2"></i>Lista de Usuarios (Estado: <?php echo $this->estadoFormulario; ?>)</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <a href="./registrarUsuario/indexRegitroUsuario.php" class="btn btn-success">
        
                        <i class="bi bi-person-add me-2"></i>Registrar Nuevo Usuario
                    </a>
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
                                $estadoVisitado = $this->acceptVisitor($usuario); // Uso de VISITOR
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
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($usuario['id_usuario']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
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
        if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
            window.location.href = `./getGestionUsuario.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>