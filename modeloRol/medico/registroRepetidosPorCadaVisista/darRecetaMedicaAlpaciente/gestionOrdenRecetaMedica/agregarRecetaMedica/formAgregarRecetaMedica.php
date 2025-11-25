<?php

session_start();
include_once('../../../../../../shared/pantalla.php');
include_once('../../../../../../modelo/RecetaMedicaDAO.php');

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Define la estructura de la página de registro.
 */
class formAgregarRecetaMedica extends pantalla
{
    // Método: `formAgregarRecetaMedicaShow` (Método del Template)
    public function formAgregarRecetaMedicaShow()
    {
        $this->cabeceraShow('Registrar Nueva Receta Médica');

        $objReceta = new RecetaMedicaDAO();
        // Atributo: $historiasClinicas
        $historiasClinicas = $objReceta->obtenerHistoriasClinicas();
        
        // Obtener información del médico logueado
        $idUsuarioMedico = $_SESSION['id_usuario'] ?? null;
        $nombreMedico = $_SESSION['login'] ?? 'Usuario no identificado';
        
        // Lógica de Permisos (Parte del Template)
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            // ... (CÓDIGO DE ACCESO DENEGADO) ...
            $this->mostrarMensaje('❌ Acceso denegado. Solo el personal médico puede registrar recetas.', '../../../../index.php', 'error');
            exit();
        }
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Receta Médica</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-person-badge me-2"></i>
                <strong>Médico:</strong> <?php echo htmlspecialchars($nombreMedico); ?>
                <span class="badge bg-primary ms-2">Médico</span>
            </div>

            <form action="./getAgregarRecetaMedica.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="historiaClinicaId" class="form-label">Historia Clínica (*):</label>
                        <select class="form-select" id="historiaClinicaId" name="historiaClinicaId" required>
                            <option value="">Seleccione Historia Clínica</option>
                            <?php 
                            // ITERATOR (Implícito): Recorrido de Historias Clínicas
                            foreach ($historiasClinicas as $hc) { ?>
                                <option value="<?php echo htmlspecialchars($hc['historia_clinica_id']); ?>">
                                    <?php echo htmlspecialchars($hc['nombre_paciente'] . ' (HC: ' . $hc['historia_clinica_id'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label">Fecha (*):</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="indicacionesGenerales" class="form-label">Indicaciones Generales (*):</label>
                    <textarea class="form-control" id="indicacionesGenerales" name="indicacionesGenerales" rows="8" required placeholder="Ingrese las indicaciones médicas completas, incluyendo medicamentos, dosis, frecuencia, duración del tratamiento, precauciones, etc."></textarea>
                    </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnAgregar" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle-fill me-2"></i>Registrar Receta
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
    charCount.textContent = '0 caracteres';
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
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}
</style>

<?php
        $this->pieShow();
    }
}
?>