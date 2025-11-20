<?php
session_start();
include_once('../../../../../../shared/pantalla.php');
include_once('../../../../../../modelo/RecetaMedicaDAO.php');

class formEditarRecetaMedica extends pantalla
{
    public function formEditarRecetaMedicaShow()
    {
        $this->cabeceraShow('Editar Receta Médica');

        // Verificar que el usuario sea médico
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Acceso denegado. Solo el personal médico puede editar recetas.', 
                '../../../../index.php', 
                'error'
            );
            exit();
        }

        // Obtener ID de la receta a editar
        $idReceta = $_GET['id'] ?? null;
        
        if (!$idReceta) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ ID de receta no proporcionado.', 
                '../indexRecetaMedica.php', 
                'error'
            );
            exit();
        }

        $objReceta = new RecetaMedicaDAO();
        $receta = $objReceta->obtenerRecetaPorId($idReceta);

        if (!$receta) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ Receta médica no encontrada.', 
                '../indexRecetaMedica.php', 
                'error'
            );
            exit();
        }

        // Verificar que el médico logueado es el dueño de la receta
        $idUsuarioMedico = $_SESSION['id_usuario'] ?? null;
        $idMedicoReceta = $receta['id_medico'];
        
        // Obtener el id_usuario del médico de la receta
        $objReceta = new RecetaMedicaDAO();
        $idUsuarioReceta = $objReceta->obtenerIdUsuarioPorIdMedico($idMedicoReceta);
        
        if ($idUsuarioReceta != $idUsuarioMedico) {
            include_once('../../../../shared/mensajeSistema.php');
            $objMensaje = new mensajeSistema();
            $objMensaje->mensajeSistemaShow(
                '❌ No tiene permisos para editar esta receta. Solo el médico que la creó puede modificarla.', 
                '../indexRecetaMedica.php', 
                'error'
            );
            exit();
        }

        $historiasClinicas = $objReceta->obtenerHistoriasClinicas();
        $nombreMedico = $_SESSION['login'] ?? 'Usuario no identificado';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Receta Médica N° <?php echo htmlspecialchars($receta['id_receta']); ?></h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-person-badge me-2"></i>
                <strong>Médico:</strong> <?php echo htmlspecialchars($nombreMedico); ?>
                <span class="badge bg-primary ms-2">Médico</span>
                <span class="badge bg-success ms-2">Creador de la receta</span>
            </div>

            <form action="./getEditarRecetaMedica.php" method="POST">
                <input type="hidden" name="idReceta" value="<?php echo htmlspecialchars($receta['id_receta']); ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="historiaClinicaId" class="form-label">Historia Clínica (*):</label>
                        <select class="form-select" id="historiaClinicaId" name="historiaClinicaId" required>
                            <option value="">Seleccione Historia Clínica</option>
                            <?php foreach ($historiasClinicas as $hc) { 
                                $selected = ($hc['historia_clinica_id'] == $receta['historia_clinica_id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($hc['nombre_paciente'] . ' (HC: ' . $hc['historia_clinica_id'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha (*):</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" 
                               value="<?php echo htmlspecialchars($receta['fecha']); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="indicacionesGenerales" class="form-label">Indicaciones Generales (*):</label>
                    <textarea class="form-control" id="indicacionesGenerales" name="indicacionesGenerales" 
                              rows="8" required 
                              placeholder="Ingrese las indicaciones médicas completas, incluyendo medicamentos, dosis, frecuencia, duración del tratamiento, precauciones, etc."><?php echo htmlspecialchars($receta['indicaciones_generales']); ?></textarea>
                    <div class="form-text">
                        <strong>Ejemplo de formato:</strong><br>
                        • Paracetamol 500mg - 1 tableta cada 8 horas por 5 días<br>
                        • Ibuprofeno 400mg - 1 tableta cada 12 horas por 3 días<br>
                        • Reposo relativo. Beber abundante líquido<br>
                        • Control en 7 días
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <i class="bi bi-info-circle me-2"></i>Información de la Receta
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Paciente:</strong> <?php echo htmlspecialchars($receta['nombre_paciente']); ?><br>
                                <strong>DNI:</strong> <?php echo htmlspecialchars($receta['dni']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($receta['creado_en'])); ?><br>
                                <strong>Médico original:</strong> <?php echo htmlspecialchars($receta['nombre_medico']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnEditar" class="btn btn-warning btn-lg">
                        <i class="bi bi-check-circle-fill me-2"></i>Actualizar Receta
                    </button>
                    <a href="../indexRecetaMedica.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-2"></i>Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación adicional del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const textarea = document.getElementById('indicacionesGenerales');
    
    // Crear contador de caracteres
    const charCount = document.createElement('div');
    charCount.className = 'form-text text-end';
    charCount.textContent = textarea.value.length + ' caracteres';
    textarea.parentNode.appendChild(charCount);
    
    // Actualizar contador
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length + ' caracteres';
        
        if (length < 10) {
            charCount.style.color = 'red';
            charCount.innerHTML = length + ' caracteres <i class="bi bi-exclamation-triangle"></i> Mínimo 10 caracteres';
        } else if (length < 50) {
            charCount.style.color = 'orange';
            charCount.innerHTML = length + ' caracteres';
        } else {
            charCount.style.color = 'green';
            charCount.innerHTML = length + ' caracteres <i class="bi bi-check-circle"></i>';
        }
    });
    
    // Validación al enviar
    form.addEventListener('submit', function(e) {
        const indicaciones = textarea.value.trim();
        
        if (indicaciones.length < 10) {
            e.preventDefault();
            alert('❌ Las indicaciones generales deben tener al menos 10 caracteres.');
            textarea.focus();
            return false;
        }
        
        return true;
    });
});
</script>

<style>
.form-control:focus, .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
}
</style>

<?php
        $this->pieShow();
    }
}
?>