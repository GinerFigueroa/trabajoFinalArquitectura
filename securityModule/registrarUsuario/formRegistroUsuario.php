<?php
include_once('../../shared/pantalla.php');
include_once('../../modelo/RolDAO.php'); 

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

class formRegistroUsuario extends pantalla
{
    private $estadoFormulario = 'inicial';

    private function setEstadoFormulario($estado) {
        $this->estadoFormulario = $estado;
    }

    public function formRegistroUsuarioShow()
    {
        $this->setEstadoFormulario('cargando');
        
        // CABECERA ESPECIAL PARA REGISTRO DE PACIENTES
        $this->cabeceraShow('Registro de Paciente - Sistema Hospitalario');

        // Obtener solo el rol de Paciente (ID 4)
        $objRol = new RolDAO();
        $rolPaciente = $objRol->obtenerRolPorId(4); // ID 4 = Paciente
        
        $this->setEstadoFormulario('mostrando');
?>
<!-- ESTILOS ESPECÍFICOS PARA REGISTRO DE PACIENTES -->
<style>
.registro-paciente {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px 0;
}
.card-registro {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.header-registro {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 15px 15px 0 0;
}
.btn-registro {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-registro:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}
.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}
.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
.login-link {
    color: #28a745;
    text-decoration: none;
    font-weight: 600;
}
.login-link:hover {
    text-decoration: underline;
}
</style>

<div class="registro-paciente">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card card-registro">
                    <div class="card-header header-registro text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Registro de Paciente
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">
                            Complete sus datos para crear una cuenta de paciente
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <!-- Información del Sistema -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle me-2"></i>Información Importante
                            </h6>
                            <ul class="mb-0 small">
                                <li>Este formulario es exclusivo para registro de pacientes</li>
                                <li>Una vez registrado, podrá solicitar citas y acceder a su historial médico</li>
                                <li>Todos los campos marcados con (*) son obligatorios</li>
                            </ul>
                        </div>

                        <form action="./getRegistrarUsuario.php" method="POST" id="formRegistroPaciente">
                            <input type="hidden" name="action" value="registrar">
                            <!-- ROL FIJO PARA PACIENTE -->
                            <input type="hidden" name="regRol" value="4">
                            <!-- ESTADO ACTIVO POR DEFECTO -->
                            <input type="hidden" name="regActivo" value="1">

                            <div class="row">
                                <!-- Información de Cuenta -->
                                <div class="col-12 mb-3">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-person-badge me-2"></i>Información de Cuenta
                                    </h6>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="regUsuario" class="form-label">
                                        Usuario <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input type="text" class="form-control" id="regUsuario" name="regUsuario" 
                                               required maxlength="50" placeholder="Ej: juan.perez">
                                    </div>
                                    <small class="form-text text-muted">Elija un nombre de usuario único</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="regEmail" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="regEmail" name="regEmail" 
                                               required placeholder="Ej: juan@example.com">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regClave" class="form-label">
                                        Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="regClave" name="regClave" 
                                               required minlength="8" placeholder="Mínimo 8 caracteres">
                                    </div>
                                    <small class="form-text text-muted">Debe contener letras y números</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regConfirmarClave" class="form-label">
                                        Confirmar Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input type="password" class="form-control" id="regConfirmarClave" 
                                               required placeholder="Repita su contraseña">
                                    </div>
                                </div>

                                <!-- Información Personal -->
                                <div class="col-12 mb-3 mt-4">
                                    <h6 class="text-primary border-bottom pb-2">
                                        <i class="bi bi-person-vcard me-2"></i>Información Personal
                                    </h6>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regNombre" class="form-label">
                                        Nombres <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-card-text"></i>
                                        </span>
                                        <input type="text" class="form-control" id="regNombre" name="regNombre" 
                                               required placeholder="Ej: Juan Carlos">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regApellidoPaterno" class="form-label">
                                        Apellido Paterno <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-card-text"></i>
                                        </span>
                                        <input type="text" class="form-control" id="regApellidoPaterno" 
                                               name="regApellidoPaterno" required placeholder="Ej: Pérez">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regApellidoMaterno" class="form-label">
                                        Apellido Materno
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-card-text"></i>
                                        </span>
                                        <input type="text" class="form-control" id="regApellidoMaterno" 
                                               name="regApellidoMaterno" placeholder="Ej: González">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="regTelefono" class="form-label">
                                        Teléfono <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-telephone"></i>
                                        </span>
                                        <input type="tel" class="form-control" id="regTelefono" name="regTelefono" 
                                               required placeholder="Ej: 987654321">
                                    </div>
                                </div>
                            </div>

                            <!-- Términos y Condiciones -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aceptoTerminos" required>
                                    <label class="form-check-label small" for="aceptoTerminos">
                                        Acepto los <a href="#" class="login-link">términos y condiciones</a> 
                                        y la <a href="#" class="login-link">política de privacidad</a>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="btnRegistrar" class="btn btn-registro btn-lg text-white">
                                    <i class="bi bi-person-plus me-2"></i>Registrarse como Paciente
                                </button>
                                
                                <div class="text-center mt-3">
                                    <p class="mb-0">
                                        ¿Ya tiene una cuenta? 
                                        <a href="../../index.php" class="login-link">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="text-center mt-4">
                    <small class="text-white-50">
                        <i class="bi bi-shield-check me-1"></i>
                        Sus datos están protegidos y se manejan con confidencialidad médica
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para validación de contraseñas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formRegistroPaciente');
    const clave = document.getElementById('regClave');
    const confirmarClave = document.getElementById('regConfirmarClave');
    
    // Validar que las contraseñas coincidan
    function validarContraseñas() {
        if (clave.value !== confirmarClave.value) {
            confirmarClave.setCustomValidity('Las contraseñas no coinciden');
            return false;
        } else {
            confirmarClave.setCustomValidity('');
            return true;
        }
    }
    
    clave.addEventListener('change', validarContraseñas);
    confirmarClave.addEventListener('keyup', validarContraseñas);
    
    // Validar fortaleza de contraseña
    clave.addEventListener('input', function() {
        const tieneNumero = /\d/.test(clave.value);
        const tieneLetra = /[a-zA-Z]/.test(clave.value);
        
        if (clave.value.length >= 8 && tieneNumero && tieneLetra) {
            clave.style.borderColor = '#28a745';
        } else {
            clave.style.borderColor = '#dc3545';
        }
    });
    
    // Mostrar mensaje de éxito al enviar
    form.addEventListener('submit', function(e) {
        if (!validarContraseñas()) {
            e.preventDefault();
            return;
        }
        
        // Mostrar loading
        const btn = form.querySelector('button[type="submit"]');
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Registrando...';
        btn.disabled = true;
    });
});
</script>

<?php
        $this->pieShow();
    }
}
?>