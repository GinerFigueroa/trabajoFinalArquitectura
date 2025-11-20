<?php

include_once("../../../shared/pantalla.php");
include_once("../../../modelo/ExamenClinicoDAO.php"); 

class formExamenEntrada extends pantalla
{
    public function formExamenEntradaShow()
    {
        $this->cabeceraShow("Gestión de Examen Clínico de Entrada");

        $objExamenDAO = new ExamenClinicoDAO(); 
        $listaExamenes = $objExamenDAO->obtenerTodosExamenes();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4><i class="bi bi-clipboard2-pulse me-2"></i>Exámenes Clínicos de Entrada</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-end">
                   
                     <a href="../gestionHistoriaClinica/indexHistoriaClinica.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a HM
                    </a>
                    <a href="./agregarExamenEntrada/indexExamenAgregar.php"class="btn btn-success me-2" >
                        <i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Examen
                    </a>
                </div>


            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>Peso (kg)</th>
                            <th>Talla (m)</th>
                            <th>Pulso</th>
                            <th>Enfermero</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($listaExamenes) > 0) {
                            foreach ($listaExamenes as $examen) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($examen['examen_id']); ?></td>
                                    <td><?php echo htmlspecialchars($examen['nombre_paciente'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($examen['peso']); ?></td>
                                    <td><?php echo htmlspecialchars($examen['talla']); ?></td>
                                    <td><?php echo htmlspecialchars($examen['pulso']); ?></td>
                                    <td><?php echo htmlspecialchars($examen['nombre_enfermero'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="./editarExamenEntrada/indexExamenEditar.php?id=<?php echo htmlspecialchars($examen['examen_id']); ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar" onclick="confirmarEliminar(<?php echo htmlspecialchars($examen['examen_id']); ?>)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay exámenes clínicos registrados.</td>
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
        if (confirm('¿Está seguro de que desea eliminar este Examen Clínico?')) {
            window.location.href = `./getExamenEntrada.php?action=eliminar&id=${id}`;
        }
    }
</script>

<?php
        $this->pieShow();
    }
}
?>