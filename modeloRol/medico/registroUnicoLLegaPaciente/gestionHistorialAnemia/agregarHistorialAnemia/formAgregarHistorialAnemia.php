<?php

include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');

/**
 * Patrón: TEMPLATE METHOD 🧱
 * Hereda de la clase 'pantalla' para definir el esqueleto de la vista.
 */
class formAgregarHistorialAnemia extends pantalla
{
    // Método: formAgregarHistorialAnemiaShow (Método del Template: Esqueleto de la página)
    public function formAgregarHistorialAnemiaShow()
    {
        // TEMPLATE METHOD: Paso 1 - Cabecera
        $this->cabeceraShow('Registrar Nuevo Historial de Anemia y Antecedentes');

        $objDAO = new HistorialAnemiaPacienteDAO();
        // Método: obtenerHistoriasClinicasDisponibles (Obtiene el conjunto de datos)
        $historiasClinicas = $objDAO->obtenerHistoriasClinicasDisponibles();
        
        // Creamos un ITERATOR para recorrer la colección de Historias Clínicas
        $iterator = new ArrayIterator($historiasClinicas);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-medical me-2"></i>Nuevo Historial de Anemia y Antecedentes</h4>
        </div>
        <div class="card-body">
            <?php if ($iterator->count() === 0) { // Iterator: Comprobación de existencia ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>No hay historias clínicas disponibles</strong><br>
                    Todas las historias clínicas ya tienen un historial de anemia registrado o no existen historias clínicas.
                    <div class="mt-3">
                        <a href="../indexHistorialAnemia.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i>Volver al Listado
                        </a>
                        <a href="../gestionHistoriaClinica/indexHistoriaClinica.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Crear Historia Clínica
                        </a>
                    </div>
                </div>
            <?php } else { ?>
            
            <form action="./getAgregarHistoriaAnemia.php" method="POST" id="formHistorial">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="historia_clinica_id" class="form-label">Seleccionar Historia Clínica (*)</label>
                        <select class="form-select" id="historia_clinica_id" name="historia_clinica_id" required>
                            <option value="">-- Seleccione una historia clínica --</option>
                            <?php 
                            // Patrón: ITERATOR 🔁: Recorrido de la colección
                            while ($iterator->valid()) { 
                                $historia = $iterator->current();
                            ?>
                                <option value="<?php echo htmlspecialchars($historia['historia_clinica_id']); ?>">
                                    <?php echo htmlspecialchars($historia['nombre_paciente'] . ' - DNI: ' . $historia['dni'] . ' (Creada: ' . date('d/m/Y', strtotime($historia['fecha_creacion'])) . ')'); ?>
                                </option>
                            <?php 
                                $iterator->next();
                            } 
                            ?>
                        </select>
                        <div class="form-text">Solo se muestran historias clínicas sin historial de anemia registrado.</div>
                    </div>
                </div>

                                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="alergias" class="form-label">Alergias Conocidas</label>
                        <textarea class="form-control" id="alergias" name="alergias" rows="3" 
                                  placeholder="Ej: Penicilina, aspirina, mariscos..."></textarea>
                        <div class="form-text">Lista de alergias a medicamentos, alimentos, etc.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="medicacion" class="form-label">Medicación Actual</label>
                        <textarea class="form-control" id="medicacion" name="medicacion" rows="3" 
                                  placeholder="Ej: Metformina 500mg, Losartán 50mg..."></textarea>
                        <div class="form-text">Medicamentos que toma actualmente el paciente.</div>
                    </div>
                </div>

                                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Enfermedades Crónicas y Antecedentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_pulmonares" class="form-label">Enfermedades Pulmonares</label>
                                <input type="text" class="form-control" id="enfermedades_pulmonares" name="enfermedades_pulmonares" 
                                       placeholder="Ej: Asma, EPOC, tuberculosis...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_cardiacas" class="form-label">Enfermedades Cardíacas</label>
                                <input type="text" class="form-control" id="enfermedades_cardiacas" name="enfermedades_cardiacas" 
                                       placeholder="Ej: Hipertensión, arritmia, cardiopatía...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_neurologicas" class="form-label">Enfermedades Neurológicas</label>
                                <input type="text" class="form-control" id="enfermedades_neurologicas" name="enfermedades_neurologicas" 
                                       placeholder="Ej: Epilepsia, migraña, Parkinson...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_hepaticas" class="form-label">Enfermedades Hepáticas</label>
                                <input type="text" class="form-control" id="enfermedades_hepaticas" name="enfermedades_hepaticas" 
                                       placeholder="Ej: Hepatitis, cirrosis, hígado graso...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_renales" class="form-label">Enfermedades Renales</label>
                                <input type="text" class="form-control" id="enfermedades_renales" name="enfermedades_renales" 
                                       placeholder="Ej: Insuficiencia renal, cálculos...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="enfermedades_endocrinas" class="form-label">Enfermedades Endocrinas</label>
                                <input type="text" class="form-control" id="enfermedades_endocrinas" name="enfermedades_endocrinas" 
                                       placeholder="Ej: Diabetes, hipotiroidismo...">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="otras_enfermedades" class="form-label">Otras Enfermedades</label>
                                <textarea class="form-control" id="otras_enfermedades" name="otras_enfermedades" rows="2" 
                                          placeholder="Otras condiciones médicas no listadas anteriormente..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Factores de Riesgo y Antecedentes Quirúrgicos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="ha_sido_operado" class="form-label">Antecedentes Quirúrgicos</label>
                                <input type="text" class="form-control" id="ha_sido_operado" name="ha_sido_operado" 
                                       placeholder="Ej: Apendicectomía (2018), cesárea (2020)...">
                                <div class="form-text">Cirugías previas y fecha aproximada.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="ha_tenido_tumor" name="ha_tenido_tumor" value="1">
                                    <label class="form-check-label" for="ha_tenido_tumor">
                                        Ha tenido tumor o cáncer
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="ha_tenido_hemorragia" name="ha_tenido_hemorragia" value="1">
                                    <label class="form-check-label" for="ha_tenido_hemorragia">
                                        Ha tenido hemorragias importantes
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="fuma" name="fuma" value="1">
                                    <label class="form-check-label" for="fuma">
                                        Fuma actualmente
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="toma_anticonceptivos" name="toma_anticonceptivos" value="1">
                                    <label class="form-check-label" for="toma_anticonceptivos">
                                        Toma anticonceptivos
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3" id="fumaFields" style="display: none;">
                            <div class="col-md-12">
                                <label for="frecuencia_fuma" class="form-label">Frecuencia de Fumar</label>
                                <input type="text" class="form-control" id="frecuencia_fuma" name="frecuencia_fuma" 
                                       placeholder="Ej: 10 cigarrillos al día, ocasionalmente...">
                            </div>
                        </div>
                    </div>
                </div>

                                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-gender-female me-2"></i>Estado Reproductivo (Pacientes Femeninas)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="esta_embarazada" name="esta_embarazada" value="1">
                                    <label class="form-check-label" for="esta_embarazada">
                                        Está embarazada actualmente
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="periodo_lactancia" name="periodo_lactancia" value="1">
                                    <label class="form-check-label" for="periodo_lactancia">
                                        En período de lactancia
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="semanasEmbarazoField" style="display: none;">
                                    <label for="semanas_embarazo" class="form-label">Semanas de Embarazo</label>
                                    <input type="number" class="form-control" id="semanas_embarazo" name="semanas_embarazo" 
                                           min="1" max="42" placeholder="Ej: 12">
                                    <div class="form-text">Aproximadamente cuántas semanas de gestación.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="../indexHistorialAnemia.php" class="btn btn-secondary me-md-2">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                    <button type="submit" name="btnAgregar" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Registrar Historial
                    </button>
                </div>
            </form>
            <?php } ?>
        </div>
    </div>
</div>

<script>
// Lógica de validación y eventos JS idéntica al original
document.getElementById('fuma').addEventListener('change', function() {
    document.getElementById('fumaFields').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('esta_embarazada').addEventListener('change', function() {
    document.getElementById('semanasEmbarazoField').style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        document.getElementById('semanas_embarazo').value = '';
    }
});

document.getElementById('formHistorial').addEventListener('submit', function(e) {
    const historiaClinica = document.getElementById('historia_clinica_id').value;
    
    if (!historiaClinica) {
        e.preventDefault();
        alert('Por favor, seleccione una historia clínica.');
        document.getElementById('historia_clinica_id').focus();
        return false;
    }
    
    const estaEmbarazada = document.getElementById('esta_embarazada').checked;
    const semanasEmbarazo = document.getElementById('semanas_embarazo').value;
    
    if (estaEmbarazada && (!semanasEmbarazo || semanasEmbarazo < 1 || semanasEmbarazo > 42)) {
        e.preventDefault();
        alert('Si la paciente está embarazada, debe especificar las semanas de gestación (1-42).');
        document.getElementById('semanas_embarazo').focus();
        return false;
    }
});
</script>

<?php
        // TEMPLATE METHOD: Paso 2 - Pie
        $this->pieShow();
    }
}
?>