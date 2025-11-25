

<?php
// FILE: formConsultarCitas.php

include_once('../../../shared/pantalla.php');
include_once('../../../modelo/citasPacientesDAO.php');

// --- PATRÓN: STATE (Implementación) ---
abstract class EstadoCita {
    // ATRIBUTO ABSTRACTO: Ninguno, solo métodos.
    abstract public function obtenerClase(): string;
}

class EstadoPendiente extends EstadoCita {
    public function obtenerClase(): string { return 'warning'; } // Estado Concreto
}
class EstadoConfirmada extends EstadoCita {
    public function obtenerClase(): string { return 'success'; } // Estado Concreto
}
class EstadoCompletada extends EstadoCita {
    public function obtenerClase(): string { return 'info'; }
}
class EstadoCancelada extends EstadoCita {
    public function obtenerClase(): string { return 'danger'; }
}
class EstadoVencida extends EstadoCita {
    public function obtenerClase(): string { return 'secondary'; }
}

// CONTEXTO
class CitaContext {
    private $estadoActual; // ATRIBUTO: Objeto de estado actual

    public function __construct(string $estado) {
        // Usa Factory para crear el estado inicial
        $this->transicionarA($estado);
    }
    
    // PATRÓN FACTORY METHOD Implícito (para crear estados)
    public function transicionarA(string $estado): void {
        switch ($estado) {
            case 'Pendiente': $this->estadoActual = new EstadoPendiente(); break;
            case 'Confirmada': $this->estadoActual = new EstadoConfirmada(); break;
            case 'Completada': $this->estadoActual = new EstadoCompletada(); break;
            case 'Cancelada': $this->estadoActual = new EstadoCancelada(); break;
            case 'Vencida': $this->estadoActual = new EstadoVencida(); break;
            default: $this->estadoActual = new EstadoVencida(); // Default
        }
    }

    public function obtenerClaseVisual(): string {
        return $this->estadoActual->obtenerClase(); // Delega el comportamiento al objeto de estado
    }
}
// --- FIN PATRÓN STATE/FACTORY ---


// --- PATRÓN: ITERATOR (Implementación) ---
class CitaIterator implements Iterator {
    private $data = []; // ATRIBUTO: Colección de citas
    private $position = 0; // ATRIBUTO: Posición actual

    public function __construct(array $data) {
        $this->data = array_values($data); // Resetear keys
    }
    
    // MÉTODOS DE LA INTERFAZ ITERATOR
    public function rewind(): void { $this->position = 0; }
    public function current(): mixed { return $this->data[$this->position]; }
    public function key(): mixed { return $this->position; }
    public function next(): void { ++$this->position; }
    public function valid(): bool { return isset($this->data[$this->position]); }
}
// --- FIN PATRÓN ITERATOR ---


class formConsultarCitas extends pantalla
{
    // ... (El resto del código HTML y JS se mantiene igual, solo cambia el PHP de la clase) ...

    /**
     * PATRÓN: TEMPLATE METHOD (Paso Concreto)
     */
    public function formConsultarCitasShow()
    {
        // PASO 1 (Heredado): Cabecera
        $this->cabeceraShow("Mis Citas Médicas");

        // Obtener id_usuario y validar
        $idUsuario = $_SESSION['id_usuario'] ?? null;
        if (!$idUsuario) {
            echo '<div class="alert alert-danger">Error: No se pudo identificar al paciente.</div>';
            $this->pieShow();
            return;
        }

        $objCitas = new CitasPacientesDAO();
        $idPaciente = $objCitas->obtenerIdPacientePorUsuario($idUsuario);
        
        if (!$idPaciente) {
            echo '<div class="alert alert-danger">Error: No se encontró información del paciente.</div>';
            $this->pieShow();
            return;
        }

        // Obtener información del paciente, citas y estadísticas
        $infoPaciente = $objCitas->obtenerInfoPaciente($idPaciente);
        $citas = $objCitas->obtenerCitasPorPaciente($idPaciente);
        $citasFuturas = $objCitas->obtenerCitasFuturas($idPaciente);
        $estadisticas = $objCitas->obtenerEstadisticasCitas($idPaciente);

        // PATRÓN ITERATOR: Inicialización para listas
        $citasFuturasIterator = new CitaIterator($citasFuturas);
        $citasHistorialIterator = new CitaIterator($citas);
?>

<div class="container mt-4">
    <?php if ($citasFuturasIterator->valid()): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-body">
                    <div class="row">
                        <?php 
                        // Uso del ITERATOR para recorrer las próximas citas
                        foreach ($citasFuturasIterator as $cita): 
                            // PATRÓN STATE: Determinar clase visual
                            $estadoContext = new CitaContext($cita['estado']);
                            $claseEstado = $estadoContext->obtenerClaseVisual();
                        ?>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?php echo $this->formatearFecha($cita['fecha_cita']); ?></strong>
                                            <span class="badge bg-<?php echo $claseEstado; ?>">
                                                <?php echo $cita['estado']; ?>
                                            </span>
                                        </div>
                                        </div>
                                    </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if ($citasHistorialIterator->valid()): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <tbody>
                                    <?php 
                                    // Uso del ITERATOR para recorrer el historial
                                    foreach ($citasHistorialIterator as $cita): 
                                        // PATRÓN STATE: Determinar clase visual para el historial
                                        $estadoContext = new CitaContext($cita['estado_visual']);
                                        $claseEstado = $estadoContext->obtenerClaseVisual();
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo $claseEstado; ?>">
                                                    <?php echo $cita['estado_visual']; ?>
                                                </span>
                                            </td>
                                            </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
        // PASO 2 (Heredado): Pie de página
        $this->pieShow();
    }

    private function formatearFecha($fecha)
    {
        return date('d/m/Y', strtotime($fecha));
    }
    
    // NOTA: Se elimina la función obtenerClaseEstado ya que es reemplazada por CitaContext (Patrón State)
}
?>