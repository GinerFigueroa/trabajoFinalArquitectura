<?php
// fromHistorialMedico.php

include_once("../../../../shared/pantalla.php");
include_once("../../../../modelo/RegistroMedicoDAO.php"); 
include_once("../../../../shared/mensajeSistema.php");

class formHistorialClinica extends pantalla
{
    private $objDAO;

    public function __construct() {
        $this->objDAO = new RegistroMedicoDAO(); 
    }

    public function formHistorialClinicaShow()
    {
        $this->cabeceraShow("Gestión de Historial Clínico");

        // Obtener TODOS los registros médicos
        $listaRegistros = $this->objDAO->obtenerTodosRegistros();
        
        // Manejo de mensajes
        $msg = $_GET['success'] ?? ($_GET['error'] ?? null);
        $tipoMsg = isset($_GET['success']) ? 'success' : (isset($_GET['error']) ? 'error' : null);
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white text-center">
            <h4><i class="bi bi-file-medical me-2"></i>Listado General de Historial Clínico</h4>
        </div>
        <div class="card-body">
            
            <?php 
            if ($msg) {
                $alertClass = ($tipoMsg == 'success') ? 'alert-success' : 'alert-danger';
                echo "<div class='alert {$alertClass}' role='alert'>" . htmlspecialchars($msg) . "</div>";
            }
            ?>

            <div class="row mb-3">
    <!-- Botón: Nuevo Registro -->
    <div class="col-md-4 text-start">
        <a href="./agregarHistorialMedico/indexAgregarHistorialMedico.php" class="btn btn-success w-100">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Registro
        </a>
    </div>

    <!-- Botón: Orden Examen Clínico -->
    <div class="col-md-4 text-center">
        <a href="../gestionOrdenExamenClinicoPaciente/indexOrdenExamenClinico.php" class="btn btn-primary w-100">
            <i class="bi bi-arrow-right-circle"></i> Orden Examen Clínico
        </a>
    </div>

    <!-- Botón: Evolución Médicas -->
    <div class="col-md-4 text-end">
        <a href="../gestionEvolucionPaciente/indexEvolucionPaciente.php" class="btn btn-primary w-100">
            <i class="bi bi-arrow-right-circle"></i> Evolución Médicas
        </a>
    </div>
</div>


            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID Registro</th>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico Tratante</th>
                            <th>Motivo Consulta</th>
                            <th>HC ID</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaRegistros) > 0) {
                            foreach ($listaRegistros as $registro) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registro['registro_medico_id']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($registro['fecha_registro'])); ?></td>
                                    <td><?php echo htmlspecialchars($registro['nombre_paciente']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['nombre_tratante']); ?></td>
                                    <td>
                                        <?php 
                                        $motivoConsulta = htmlspecialchars($registro['motivo_consulta'] ?? '');
                                        echo strlen($motivoConsulta) > 50 ? substr($motivoConsulta, 0, 50) . '...' : $motivoConsulta;
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">HC-<?php echo htmlspecialchars($registro['historia_clinica_id']); ?></span>
                                    </td>
                                    <td>
                                       

                                            <a href="./editarHistorialMedico/indexEditarHistorialMedico.php?reg_id=<?php echo htmlspecialchars($registro['registro_medico_id']); ?>" 
                                            class="btn btn-sm btn-warning text-white" title="Editar registro">
                                            <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                                <!-- BOTÓN PARA GENERAR PDF -->
                                                <a href="./gereraHistorialPacientePDF/indexGenerarHistorialPacientePDF.php?id=<?php echo htmlspecialchars($registro['registro_medico_id']); ?>" 
                                                target="_blank" class="btn btn-info" title="Generar PDF">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>

                                        <button onclick="confirmarEliminarRegistro(<?php echo htmlspecialchars($registro['registro_medico_id']); ?>)" 
                                                class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    No hay registros médicos en el historial clínico.
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
    function confirmarEliminarRegistro(idRegistro) {
        if (confirm('¿Está seguro de que desea ELIMINAR este registro médico?\n\nEsta acción es irreversible.')) {
            window.location.href = `getHistorialMedico.php?action=eliminar&reg_id=${idRegistro}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>