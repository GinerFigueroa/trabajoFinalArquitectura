<?php

include_once('../../../shared/pantalla.php');
include_once("../../../modelo/HistoriaClinicaDAO.php"); 

class formHistorialClinica extends pantalla
{
    public function formHistorialClinicaShow()
    {
        // Se asume que la sesión ya está iniciada y $_SESSION['id_usuario'] es el ID del Enfermero
        $idEnfermero = $_SESSION['id_usuario']; 

        $this->cabeceraShow("Historias Clínicas Asignadas");

        $objHistoriaDAO = new HistoriaClinicaDAO(); 

        // 1. Obtiene las historias donde el dr_tratante_id es el Enfermero logueado
        $listaHistorias = $objHistoriaDAO->obtenerHistoriasPorEnfermero($idEnfermero);
        
        // 2. Obtiene el nombre del usuario logueado (el Enfermero).
        $nombreEnfermero = $objHistoriaDAO->obtenerNombreCompletoUsuario($idEnfermero);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-file-medical me-2"></i>Historial Clínico de Pacientes Asignados</h4>
            <p class="mb-0">Enfermero/a: **<?php echo htmlspecialchars($nombreEnfermero); ?>** (ID: <?php echo htmlspecialchars($idEnfermero); ?>)</p>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="text-center">
                    <a href="../gestionExamenDeEntrada/indexExamenEntrada.php" class="btn btn-primary me-2">
                        <i class="bi bi-arrow-right-circle"></i> Acceder al Examen de Entrada
                    </a>
                    <a href="./agregarNuevaHistorialPaciente/indexAgregarHistorialPaciente.php" class="btn btn-success">
                        <i class="bi bi-file-plus me-2"></i> Crear Nueva Historia Clínica
                    </a>
                </div>

            </div>
            

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID HC</th>
                            <th>Paciente</th>
                            <th>Fecha Creación</th>
                            <th>Motivo Consulta (Resumen)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaHistorias) > 0) {
                            foreach ($listaHistorias as $historia) { 
                                $fecha_formateada = date('d/m/Y', strtotime($historia['fecha_creacion']));
                                
                                $motivo_consulta = $historia['motivo_consulta'] ?? 'N/A (Pendiente)';
                                $motivo_resumen = htmlspecialchars(substr($motivo_consulta, 0, 50) . (strlen($motivo_consulta) > 50 ? '...' : ''));
                                
                                $nombre_paciente = htmlspecialchars($historia['nombre_paciente']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($historia['historia_clinica_id']); ?></td>
                                    <td><?php echo $nombre_paciente; ?></td>
                                    <td><?php echo $fecha_formateada; ?></td>
                                    <td><?php echo $motivo_resumen; ?></td>
                                    <td>
                                        <a href="./editarHistorialPaciente/indexEditarHistorialPaciente.php?id=<?php echo htmlspecialchars($historia['historia_clinica_id']); ?>" class="btn btn-sm btn-warning" title="Editar Anamnesis/Registro">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($historia['historia_clinica_id']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay historias clínicas asignadas a usted.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        if (confirm('ADVERTENCIA: ¿Está seguro de que desea ELIMINAR COMPLETAMENTE esta Historia Clínica y todos sus registros asociados? Esta acción es irreversible.')) {
            window.location.href = `./getHistorialClinica.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>