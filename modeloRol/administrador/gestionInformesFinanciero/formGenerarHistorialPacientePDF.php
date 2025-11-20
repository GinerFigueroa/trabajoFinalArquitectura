<?php
include_once("../../../shared/pantalla.php");
include_once("../../../modelo/BoletaDAO.php");
include_once("../../../modelo/PacienteDAO.php");

class formGenerarHistorialPacientePDF extends pantalla
{
    public function formGenerarHistorialPacientePDFShow()
    {
        $this->cabeceraShow("Generar Historial Financiero - PDF");

        $objAuxiliar = new PacienteDAO();
        $pacientes = $objAuxiliar->obtenerTodosPacientes();
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-earmark-pdf-fill me-2"></i>Generar Historial Financiero en PDF</h4>
        </div>
        <div class="card-body">
            <form action="./getGenerarHistorialPacientePDF.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idPaciente" class="form-label">Seleccionar Paciente:</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">-- Seleccione un Paciente --</option>
                            <?php foreach ($pacientes as $paciente) { ?>
                                <option value="<?php echo htmlspecialchars($paciente['id_paciente']); ?>">
                                    <?php echo htmlspecialchars($paciente['nombre_completo'] . " - DNI: " . $paciente['dni']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="tipoReporte" class="form-label">Tipo de Reporte:</label>
                        <select class="form-select" id="tipoReporte" name="tipoReporte" required>
                            <option value="completo">Reporte Completo</option>
                            <option value="ultimos_3_meses">Últimos 3 Meses</option>
                            <option value="ultimo_mes">Último Mes</option>
                            <option value="personalizado">Rango Personalizado</option>
                        </select>
                    </div>
                </div>

                <div class="row" id="rangoFechas" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fechaFin" class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" id="fechaFin" name="fechaFin">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="incluirResumen" class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="incluirResumen" name="incluirResumen" checked>
                        Incluir Resumen Financiero
                    </label>
                </div>

                <div class="mb-3">
                    <label for="incluirDetalles" class="form-check-label">
                        <input type="checkbox" class="form-check-input" id="incluirDetalles" name="incluirDetalles" checked>
                        Incluir Detalles de Boletas
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generar PDF
                    </button>
                    <a href="../indexHistoriaClinica.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Menú
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('tipoReporte').addEventListener('change', function() {
        const rangoFechas = document.getElementById('rangoFechas');
        if (this.value === 'personalizado') {
            rangoFechas.style.display = 'flex';
        } else {
            rangoFechas.style.display = 'none';
        }
    });

    // Establecer fechas por defecto para rangos comunes
    document.getElementById('tipoReporte').addEventListener('change', function() {
        const hoy = new Date();
        const fechaInicio = document.getElementById('fechaInicio');
        const fechaFin = document.getElementById('fechaFin');
        
        fechaFin.value = hoy.toISOString().split('T')[0];
        
        switch(this.value) {
            case 'ultimos_3_meses':
                const tresMesesAtras = new Date();
                tresMesesAtras.setMonth(hoy.getMonth() - 3);
                fechaInicio.value = tresMesesAtras.toISOString().split('T')[0];
                break;
            case 'ultimo_mes':
                const unMesAtras = new Date();
                unMesAtras.setMonth(hoy.getMonth() - 1);
                fechaInicio.value = unMesAtras.toISOString().split('T')[0];
                break;
        }
    });
</script>
<?php
        $this->pieShow();
    }
}
?>