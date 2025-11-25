<?php
// Archivo: comando/ComandoGenerarPDF.php (Lógica de Model/Command)

use Dompdf\Dompdf;

// Asegúrate de que las clases se incluyan en el archivo principal.

/**
 * Patrón Command (Comando Abstracto).
 * Define la interfaz para ejecutar la operación.
 */
abstract class ComandoGenerarPDF
{
    // Atributo Abstracto: Los datos necesarios para el comando
    protected $idInternado;
    protected $objDAO;
    protected $objMensaje;

    // Metodo: Constructor (Inicializa el contexto/datos del Command)
    public function __construct($idInternado) 
    {
        $this->idInternado = $idInternado;
        $this->objDAO = new InternadoPDFseguimientoDAO(); // Modelo de datos (Receiver)
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Metodo Abstracto: Ejecutar. 
     * Implementa la acción que el cliente pide (generar PDF).
     */
    abstract public function ejecutar();

    // Metodos: Auxiliares (Lógica común de renderizado)
    protected function renderizarPDF($html, $nombreArchivo)
    {
        $dompdf = new Dompdf();
        // ... (resto de la lógica de renderizado PDF)
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = preg_replace('/[^a-zA-Z0-9]/', '_', $nombreArchivo) . "_" . date('Ymd_His') . ".pdf";
        
        $dompdf->stream($nombreArchivo, [
            "Attachment" => true,
            "compress" => true
        ]);
    }
}

/**
 * Comando Concreto (Concrete Command) para generar el PDF completo.
 */
class PDFCompletoCommand extends ComandoGenerarPDF
{
    // Atributo: No es necesario aumentar atributos, usa los protegidos de la clase base.

    /**
     * Metodo: ejecutar().
     * Contiene la lógica específica para el reporte completo.
     */
    public function ejecutar()
    {
        // 1. Obtener Datos (Usa el Receiver: $this->objDAO)
        $datos = $this->objDAO->obtenerDatosCombinados($this->idInternado);
        
        if (!$datos || !$datos['internado']) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        // 2. Generar el HTML (Lógica de Presentación del PDF)
        $html = $this->generarHTMLCompleto($datos);

        // 3. Renderizar (Llamada al método común)
        $this->renderizarPDF($html, "Internado_Completo_" . $datos['internado']['id_internado']);
    }

    /**
     * Lógica de presentación (genera el código HTML específico para el PDF completo).
     * Nota: Esto sigue siendo parte del Command porque genera el "cuerpo" de la acción.
     */
    private function generarHTMLCompleto($datos)
    {
        $internado = $datos['internado'];
        $seguimientos = $datos['seguimientos'];
        // ... (toda la lógica HTML proporcionada en el código original)
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Historial Completo - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
            <style>
                body { 
                    font-family: 'DejaVu Sans', Arial, sans-serif; 
                    font-size: 11px; 
                    line-height: 1.3;
                    color: #333;
                    margin: 0;
                    padding: 15px;
                }
                /* ... (resto de estilos para el PDF) ... */
            </style>
        </head>
        <body>
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div style="margin-top: 8px; font-size: 14px; font-weight: bold;">
                    HISTORIAL MÉDICO COMPLETO - INTERNADO
                </div>
            </div>

            <div class="section">
                <div class="section-title">INFORMACIÓN DEL PACIENTE</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">Paciente:</div>
                        <div><?php echo htmlspecialchars($internado['nombre_completo_paciente'] ?? 'N/A'); ?></div>
                    </div>
                    </div>
            </div>

            <div class="section">
                <div class="section-title">DATOS DEL INTERNADO</div>
                </div>

            <div class="section">
                <div class="section-title">DIAGNÓSTICO Y OBSERVACIONES</div>
                </div>

            <?php if (!empty($seguimientos)): ?>
            <div class="section">
                <div class="section-title">EVOLUCIÓN CLÍNICA Y SEGUIMIENTOS (<?php echo count($seguimientos); ?> registros)</div>
                <?php foreach ($seguimientos as $index => $seguimiento): ?>
                    <div class="seguimiento-item">
                        <div style="font-weight: bold; color: #1e3c72; margin-bottom: 5px;">
                            Fecha: <?php echo date('d/m/Y H:i', strtotime($seguimiento['fecha'])); ?>
                            <?php if ($seguimiento['nombre_medico']): ?>
                                - Médico: <?php echo htmlspecialchars($seguimiento['nombre_medico']); ?>
                            <?php endif; ?>
                        </div>
                        </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            </body>
        </html>
        <?php
        return ob_get_clean();
    }
}

// Nota: Las clases PDFResumenCommand y PDFSoloSeguimientosCommand
// se definirían aquí, extendiendo ComandoGenerarPDF y 
// sobreescribiendo el método ejecutar() y un nuevo método generarHTML.