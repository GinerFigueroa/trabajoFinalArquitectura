<?php

include_once('../../../../../shared/pantalla.php');
include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');

class formEditarHistorialAnemia extends pantalla
{
    // Método: `formEditarHistorialAnemiaShow` (Carga la vista)
    public function formEditarHistorialAnemiaShow()
    {
        $this->cabeceraShow('Editar Historial de Anemia y Antecedentes');

        // Obtención de ID
        $idAnamnesis = $_GET['id'] ?? null;

        if (!$idAnamnesis) {
            echo '<div class="alert alert-danger" role="alert">ID de historial no proporcionado.</div>';
            $this->pieShow();
            return;
        }

        // Acceso directo al Modelo (DAO) para obtener los datos a mostrar
        // Atributo: `$objHistorial`
        $objHistorial = new HistorialAnemiaPacienteDAO();
        // Atributo: `$historial`
        $historial = $objHistorial->obtenerHistorialPorId($idAnamnesis);

        if (!$historial) {
            echo '<div class="alert alert-danger" role="alert">Historial de anemia no encontrado.</div>';
            $this->pieShow();
            return;
        }
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4><i class="bi bi-pencil-square me-2"></i>Editar Historial de Anemia y Antecedentes</h4>
        </div>
        <div class="card-body">
            <form action="./getEditarHistorialAnemia.php" method="POST" id="formHistorial">
                <input type="hidden" name="anamnesis_id" value="<?php echo htmlspecialchars($historial['anamnesis_id']); ?>">
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Paciente:</strong> <?php echo htmlspecialchars($historial['nombre_paciente']); ?><br>
                            <strong>DNI:</strong> <?php echo htmlspecialchars($historial['dni']); ?><br>
                            <strong>Historia Clínica ID:</strong> <?php echo htmlspecialchars($historial['historia_clinica_id']); ?>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="alergias" class="form-label">Alergias Conocidas</label>
                        <textarea class="form-control" id="alergias" name="alergias" rows="3" 
                                     placeholder="Ej: Penicilina, aspirina, mariscos..."><?php echo htmlspecialchars($historial['alergias'] ?? ''); ?></textarea>
                        <div class="form-text">Lista de alergias a medicamentos, alimentos, etc.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="medicacion" class="form-label">Medicación Actual</label>
                        <textarea class="form-control" id="medicacion" name="medicacion" rows="3" 
                                     placeholder="Ej: Metformina 500mg, Losartán 50mg..."><?php echo htmlspecialchars($historial['medicacion'] ?? ''); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($historial['enfermedades_pulmonares'] ?? ''); ?>"
                                       placeholder="Ej: Asma, EPOC, tuberculosis...">
                            </div>
                            </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="otras_enfermedades" class="form-label">Otras Enfermedades</label>
                                <textarea class="form-control" id="otras_enfermedades" name="otras_enfermedades" rows="2" 
                                             placeholder="Otras condiciones médicas no listadas anteriormente..."><?php echo htmlspecialchars($historial['otras_enfermedades'] ?? ''); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($historial['ha_sido_operado'] ?? ''); ?>"
                                       placeholder="Ej: Apendicectomía (2018), cesárea (2020)...">
                                <div class="form-text">Cirugías previas y fecha aproximada.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="ha_tenido_tumor" name="ha_tenido_tumor" value="1" 
                                         <?php echo $historial['ha_tenido_tumor'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="ha_tenido_tumor">Ha tenido tumor o cáncer</label>
                                </div>
                                </div>
                        </div>
                        
                        <div class="row mt-3" id="fumaFields" style="display: <?php echo $historial['fuma'] ? 'block' : 'none'; ?>;">
                            <div class="col-md-12">
                                <label for="frecuencia_fuma" class="form-label">Frecuencia de Fumar</label>
                                <input type="text" class="form-control" id="frecuencia_fuma" name="frecuencia_fuma" 
                                       value="<?php echo htmlspecialchars($historial['frecuencia_fuma'] ?? ''); ?>"
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
                                    <input class="form-check-input" type="checkbox" id="esta_embarazada" name="esta_embarazada" value="1"
                                         <?php echo $historial['esta_embarazada'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="esta_embarazada">Está embarazada actualmente</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="periodo_lactancia" name="periodo_lactancia" value="1"
                                         <?php echo $historial['periodo_lactancia'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="periodo_lactancia">En período de lactancia</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="semanasEmbarazoField" style="display: <?php echo $historial['esta_embarazada'] ? 'block' : 'none'; ?>;">
                                    <label for="semanas_embarazo" class="form-label">Semanas de Embarazo</label>
                                    <input type="number" class="form-control" id="semanas_embarazo" name="semanas_embarazo" 
                                           min="1" max="42" value="<?php echo htmlspecialchars($historial['semanas_embarazo'] ?? ''); ?>"
                                           placeholder="Ej: 12">
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
                    <button type="submit" name="btnEditar" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Actualizar Historial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Lógica de JavaScript para mostrar/ocultar campos condicionales y validación front-end.
document.getElementById('fuma').addEventListener('change', function() {
    document.getElementById('fumaFields').style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        document.getElementById('frecuencia_fuma').value = '';
    }
});

document.getElementById('esta_embarazada').addEventListener('change', function() {
    document.getElementById('semanasEmbarazoField').style.display = this.checked ? 'block' : 'none';
    if (!this.checked) {
        document.getElementById('semanas_embarazo').value = '';
    }
});

document.getElementById('formHistorial').addEventListener('submit', function(e) {
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
        $this->pieShow();
    }
}
?>