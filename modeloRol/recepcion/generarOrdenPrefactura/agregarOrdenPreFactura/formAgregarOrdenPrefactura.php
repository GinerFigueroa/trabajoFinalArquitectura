<?php

include_once('../../../../shared/pantalla.php');

include_once('../../../../modelo/OrdenPagoDAO.php'); 



class formAgregarOrdenPrefactura extends pantalla
{
    public function formAgregarOrdenPrefacturaShow()
    {
        $this->cabeceraShow('Registrar Nueva Orden de Prefactura');

        // ✅ Uso de la clase Paciente, definida dentro del archivo OrdenPagoDAO.php
        $objPaciente = new Paciente();
        // ID_ROL = 4 es el rol del Paciente
        $pacientesDisponibles = $objPaciente->obtenerPacientesPorRol(4); 
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white text-center">
            <h4><i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Orden de Prefactura</h4>
        </div>
        <div class="card-body">
            <form action="./getAgregarOrdenPreFactura.php" method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="idPaciente" class="form-label">Paciente (*):</label>
                        <select class="form-select" id="idPaciente" name="idPaciente" required>
                            <option value="">Seleccione Paciente</option>
                            <?php foreach ($pacientesDisponibles as $p) { ?>
                                <option value="<?php echo htmlspecialchars($p['id_paciente']); ?>">
                                    <?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido_paterno'] . ' (DNI: ' . $p['dni'] . ')'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idCita" class="form-label">Cita Pendiente (Opcional):</label>
                        <select class="form-select" id="idCita" name="idCita">
                        </select>
                        <small class="form-text text-muted">Seleccione una cita completada sin orden de pago.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idInternado" class="form-label">Internamiento Activo/Alta (Opcional):</label>
                        <select class="form-select" id="idInternado" name="idInternado">
                        </select>
                        <small class="form-text text-muted">Seleccione un internamiento activo o ya dado de alta.</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="concepto" class="form-label">Concepto / Detalle (*):</label>
                    <textarea class="form-control" id="concepto" name="concepto" rows="3" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="monto" class="form-label">Monto Estimado (S/) (*):</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" required min="0.01">
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="btnAgregar" class="btn btn-success">Generar Prefactura Pendiente</button>
                    <a href="../indexOdenPrefactura.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('idPaciente').addEventListener('change', function() {
    const idPaciente = this.value;
    const selectCitas = document.getElementById('idCita');
    const selectInternados = document.getElementById('idInternado');
    
    // Limpiar selects e inicializarlos
    selectCitas.innerHTML = '<option value="">-- Seleccionar Cita --</option>';
    selectInternados.innerHTML = '<option value="">-- Seleccionar Internamiento --</option>';

    if (idPaciente) {
        // Lógica AJAX para cargar Citas
        fetch('getAgregarOrdenPreFactura.php?action=citas&id=' + idPaciente)
            .then(response => response.json())
            .then(data => {
                data.forEach(cita => {
                    
                    // --- CORRECCIÓN CRUCIAL DE FORMATO DE FECHA ---
                    // 1. Reemplazar el espacio con 'T' para forzar el formato ISO 8601, que es más universal.
                    const fechaISO = cita.fecha_hora.replace(' ', 'T');
                    const fechaObj = new Date(fechaISO);
                    
                    let fechaVisible = cita.fecha_hora; // Usar el string original como fallback
                    
                    if (!isNaN(fechaObj)) {
                        // 2. Si la fecha es válida, la formateamos
                        fechaVisible = fechaObj.toLocaleString('es-ES', { 
                            day: '2-digit', month: '2-digit', year: 'numeric', 
                            hour: '2-digit', minute: '2-digit' 
                        });
                    }
                    // --- FIN CORRECCIÓN ---
                    
                    const option = document.createElement('option');
                    option.value = cita.id_cita;
                    // Construcción completa del texto de la cita
                    option.textContent = `Cita #${cita.id_cita} - ${fechaVisible} (${cita.nombre_tratamiento} - Dr/a. ${cita.nombre_medico})`;
                    selectCitas.appendChild(option);
                });
            })
            // Es buena práctica añadir un catch para ver si falla la conexión
            .catch(error => console.error('Error al cargar citas o JSON inválido:', error)); 

        // Lógica AJAX para cargar Internamientos (Sin cambios)
        fetch('getAgregarOrdenPreFactura.php?action=internados&id=' + idPaciente)
            .then(response => response.json())
            .then(data => {
                data.forEach(internado => {
                    const option = document.createElement('option');
                    option.value = internado.id_internado;
                    option.textContent = `Int. #${internado.id_internado} - Hab. ${internado.habitacion_numero} (${internado.estado})`;
                    selectInternados.appendChild(option);
                });
            });
    }
    
    // El bloque fetch extra de citas al final puede eliminarse ya que es redundante,
    // pero lo dejo sin modificar para cumplir la solicitud de no cambiar la lógica de la vista si funciona.

    fetch('getAgregarOrdenPreFactura.php?action=citas&id=' + idPaciente)
    .then(response => {
        console.log('Respuesta de la red:', response);
        if (!response.ok) {
            throw new Error('Error en la respuesta de la red');
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos de citas recibidos:', data);
        if (Array.isArray(data)) {
            if (data.length === 0) {
                console.log('No hay citas para este paciente.');
                // Añadir una opción que diga que no hay citas?
                const option = document.createElement('option');
                option.textContent = 'No hay citas completadas';
                option.value = '';
                selectCitas.appendChild(option);
            } else {
                data.forEach(cita => {
                    console.log('Procesando cita:', cita);
                    const option = document.createElement('option');
                    option.value = cita.id_cita;
                    // Formatear fecha
                    const fecha = new Date(cita.fecha_hora.replace(' ', 'T'));
                    const fechaFormateada = fecha.toLocaleString('es-ES');
                    option.textContent = `Cita #${cita.id_cita} - ${fechaFormateada} (${cita.nombre_tratamiento}) - Dr. ${cita.nombre_medico}`;
                    selectCitas.appendChild(option);
                });
            }
        } else {
            console.error('La respuesta no es un array:', data);
        }
    })
    .catch(error => {
        console.error('Error al cargar citas:', error);
    });

});
</script>

<?php
        $this->pieShow();
    }
}
?>