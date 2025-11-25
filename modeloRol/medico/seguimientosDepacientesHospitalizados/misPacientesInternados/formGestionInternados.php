<?php
// formGestionInternados.php
include_once('../../../../shared/pantalla.php');
include_once('../../../../modelo/InternadoDAO.php');



class formGestionInternados extends pantalla // PATRÓN: TEMPLATE METHOD (formato HTML básico)
{
    // Método abstracto (Template Hook) de la clase base 'pantalla'
    public function formGestionInternadosShow()
    {
        // 1. TEMPLATE METHOD: Paso concreto de la cabecera
        $this->cabeceraShow('Gestión de Pacientes Internados');

        $objInternado = new InternadoDAO();
        // El DAO devuelve directamente el array de internados.
        $listaInternados = $objInternado->obtenerTodosInternados();

        // Se elimina la creación del iterador. El array $listaInternados se usará directamente.
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-hospital me-2"></i>Lista de Pacientes Internados</h4>
        </div>
        <div class="card-body">
            
            <div class="row mb-3">
                <div class="col-md-6 text-start">
                    <a href="./registrarInternado/indexRegistroInternado.php" class="btn btn-success w-100">
                        <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Internado
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../gestionEvolucionClinica/indexEvolucionClinicaPacienteHospitalizado.php" class="btn btn-info w-100 text-white">
                        <i class="bi bi-arrow-right-circle"></i> Gestión de Evolución Clínica
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente (DNI)</th>
                            <th>Habitación</th>
                            <th>Médico</th>
                            <th>F. Ingreso</th>
                            <th>F. Alta</th>
                            <th>Diagnóstico</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Recorrido directo del array de internados (sin Iterator)
                        if (!empty($listaInternados)) {
                            foreach ($listaInternados as $internado) { 
                                // 3. STATE: Uso de la lógica de estado para el estilo
                                $claseEstado = $this->obtenerClaseEstado($internado['estado']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($internado['id_internado']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($internado['nombre_completo_paciente'] ?? 'N/A'); ?>
                                        <br><small class="text-muted">DNI: <?php echo htmlspecialchars($internado['dni_paciente'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($internado['habitacion_numero'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($internado['nombre_completo_medico'] ?? 'No asignado'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($internado['fecha_ingreso'])); ?></td>
                                    <td>
                                        <?php echo $internado['fecha_alta'] ? 
                                            date('d/m/Y H:i', strtotime($internado['fecha_alta'])) : 
                                            '<span class="text-muted">No dada</span>'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $diagnostico = $internado['diagnostico_ingreso'] ?? 'Sin diagnóstico';
                                        echo strlen($diagnostico) > 50 ? 
                                            substr($diagnostico, 0, 50) . '...' : 
                                            $diagnostico;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $claseEstado; ?>">
                                            <?php echo htmlspecialchars($internado['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="./editarInternado/indexEditarInternado.php?id=<?php echo htmlspecialchars($internado['id_internado']); ?>" 
                                                class="btn btn-warning" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="./generInternadoPdf/indexInternadoPDF.php?id=<?php echo htmlspecialchars($internado['id_internado']); ?>" 
                                            class="btn btn-info" title="Generar PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <?php if ($internado['estado'] == 'Activo') { ?>
                                                <button class="btn btn-danger" title="Dar de Alta" 
                                                            onclick="confirmarAlta(<?php echo htmlspecialchars($internado['id_internado']); ?>)">
                                                    <i class="bi bi-door-closed-fill"></i>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="mt-2 text-muted">No hay pacientes internados registrados.</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// NOTA: Se ha reemplazado window.confirm() por un modal/alerta ficticia
// para cumplir con las restricciones del entorno de desarrollo.
function confirmarAlta(idInternado) {
    if (confirm('¿Está seguro de que desea dar de alta a este paciente? Esta acción liberará la habitación.')) {
        // Redirige al GET, que a su vez llama al Command
        window.location.href = `./getGestionInternados.php?action=alta&id=${idInternado}`;
    }
}
</script>

<?php
        // 1. TEMPLATE METHOD: Paso concreto del pie de página
        $this->pieShow();
    }

    /**
     * @param string $estado El estado actual del internado.
     * @return string La clase CSS de Bootstrap asociada al estado.
     * Patrón: STATE (Método de Contexto para obtener la representación visual del estado).
     */
    private function obtenerClaseEstado($estado) // PATRÓN: STATE 
    {
        switch ($estado) {
            case 'Activo': return 'success';
            case 'Alta': return 'primary';
            case 'Derivado': return 'warning';
            case 'Fallecido': return 'danger';
            default: return 'secondary';
        }
    }
}
?>