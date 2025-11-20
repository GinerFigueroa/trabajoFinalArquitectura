<?php
session_start();

require_once('../../../../../dompdf/autoload.inc.php');
use Dompdf\Dompdf;

include_once("../../../../../modelo/InternadoDAO.php");
include_once("../../../../../shared/mensajeSistema.php");

class controlInternadoPDF
{
    private $objDAO;
    private $objMensaje;

    public function __construct() {
        $this->objDAO = new InternadoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function procesarSolicitud()
    {
        $action = $_GET['action'] ?? '';
        $idInternado = $_GET['id'] ?? null;

        if (!$idInternado || !is_numeric($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("ID de internado no válido.", "../indexGestionInternados.php", "error");
            return;
        }

        switch ($action) {
            case 'completo':
                $this->generarPDFCompleto($idInternado);
                break;
            case 'resumen':
                $this->generarResumenClinico($idInternado);
                break;
            case 'facturacion':
                $this->generarFacturacion($idInternado);
                break;
            default:
                $this->objMensaje->mensajeSistemaShow("Acción no válida.", "../indexGestionInternados.php", "error");
        }
    }

    private function generarPDFCompleto($idInternado)
    {
        $internado = $this->objDAO->obtenerInternadoPorId($idInternado);
        
        if (!$internado) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLCompleto($internado);
        $this->renderizarPDF($html, "Internado_Completo_" . $internado['id_internado']);
    }

    private function generarResumenClinico($idInternado)
    {
        $internado = $this->objDAO->obtenerInternadoPorId($idInternado);
        
        if (!$internado) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLResumen($internado);
        $this->renderizarPDF($html, "Resumen_Clinico_" . $internado['id_internado']);
    }

    private function generarFacturacion($idInternado)
    {
        $internado = $this->objDAO->obtenerInternadoPorId($idInternado);
        
        if (!$internado) {
            $this->objMensaje->mensajeSistemaShow("No se encontró el internado especificado.", "../indexGestionInternados.php", "error");
            return;
        }

        $html = $this->generarHTMLFacturacion($internado);
        $this->renderizarPDF($html, "Facturacion_" . $internado['id_internado']);
    }

    private function generarHTMLCompleto($internado)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Reporte Completo - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
            <style>
                body { 
                    font-family: 'DejaVu Sans', Arial, sans-serif; 
                    font-size: 12px; 
                    line-height: 1.4;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 25px;
                    border-bottom: 3px double #1e3c72;
                    padding-bottom: 15px;
                }
                .hospital-title {
                    color: #1e3c72;
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .section {
                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                .section-title {
                    background-color: #1e3c72;
                    color: white;
                    padding: 8px 12px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    border-radius: 4px;
                }
                .patient-info {
                    border: 1px solid #ddd;
                    padding: 15px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                }
                .info-row {
                    margin-bottom: 8px;
                    display: flex;
                }
                .info-label {
                    font-weight: bold;
                    min-width: 150px;
                    color: #1e3c72;
                }
                .table-medical {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .table-medical th {
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    padding: 8px;
                    text-align: left;
                    font-weight: bold;
                }
                .table-medical td {
                    border: 1px solid #dee2e6;
                    padding: 8px;
                    vertical-align: top;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 10px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <!-- Encabezado -->
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div style="font-size: 14px; color: #2a5298; margin-bottom: 10px;">
                    90 años cuidando tu salud y la de los tuyos
                </div>
                <div style="margin-top: 10px; font-size: 16px; font-weight: bold;">
                    REPORTE COMPLETO DE INTERNADO
                </div>
            </div>

            <!-- Información del Internado -->
            <div class="section">
                <div class="section-title">INFORMACIÓN DEL INTERNADO</div>
                <div class="patient-info">
                    <div class="info-row">
                        <div class="info-label">ID Internado:</div>
                        <div>#<?php echo htmlspecialchars($internado['id_internado']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Paciente:</div>
                        <div><?php echo htmlspecialchars($internado['nombre_paciente'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">FechaIngreso:</div>
                        <div><?php echo date('d/m/Y H:i', strtotime($internado['fecha_ingreso'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha Alta:</div>
                        <div><?php echo $internado['fecha_alta'] ? date('d/m/Y H:i', strtotime($internado['fecha_alta'])) : 'Pendiente'; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Habitación:</div>
                        <div><?php echo htmlspecialchars($internado['habitacion_numero'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Diagnóstico:</div>
                        <div><?php echo htmlspecialchars($internado['diagnostico_ingreso'] ?? 'No especificado'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado:</div>
                        <div><?php echo htmlspecialchars($internado['estado']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Observaciones:</div>
                        <div><?php echo htmlspecialchars($internado['observaciones'] ?? 'No hay observaciones'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Firmas -->
            <div class="section">
                <div style="margin-top: 50px; border-top: 1px solid #333; padding-top: 10px; text-align: center;">
                    <div style="display: flex; justify-content: space-around; margin-top: 30px;">
                        <div style="text-align: center;">
                            <div style="border-top: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                            <div>Médico Tratante</div>
                            <div style="font-size: 10px; color: #666;">Clínica González</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="border-top: 1px solid #333; width: 200px; margin-bottom: 5px;"></div>
                            <div>Jefe de Servicio</div>
                            <div style="font-size: 10px; color: #666;">Clínica González</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie de Página -->
            <div class="footer">
                <div>Documento generado el: <?php echo date('d/m/Y H:i'); ?></div>
                <div>Clínica González - Servicio de Internamiento</div>
                <div>Documento confidencial - Prohibida su reproducción no autorizada</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function generarHTMLResumen($internado)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Resumen Clínico - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
            <style>
                body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e3c72; padding-bottom: 10px; }
                .hospital-title { color: #1e3c72; font-size: 18px; font-weight: bold; }
                .section { margin-bottom: 15px; }
                .section-title { background-color: #28a745; color: white; padding: 6px 10px; font-weight: bold; margin-bottom: 8px; }
                .info-row { margin-bottom: 5px; display: flex; }
                .info-label { font-weight: bold; min-width: 120px; color: #1e3c72; }
                .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div style="font-size: 12px;">RESUMEN CLÍNICO DE INTERNADO</div>
            </div>

            <div class="section">
                <div class="section-title">DATOS PRINCIPALES</div>
                <div class="info-row"><div class="info-label">Paciente:</div><div><?php echo htmlspecialchars($internado['nombre_paciente'] ?? 'N/A'); ?></div></div>
                <div class="info-row"><div class="info-label">Internado ID:</div><div>#<?php echo htmlspecialchars($internado['id_internado']); ?></div></div>
                <div class="info-row"><div class="info-label">FechaIngreso:</div><div><?php echo date('d/m/Y', strtotime($internado['fecha_ingreso'])); ?></div></div>
                <div class="info-row"><div class="info-label">Estado:</div><div><?php echo htmlspecialchars($internado['estado']); ?></div></div>
            </div>

            <div class="section">
                <div class="section-title">DIAGNÓSTICO</div>
                <div style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <?php echo htmlspecialchars($internado['diagnostico_ingreso'] ?? 'No especificado'); ?>
                </div>
            </div>

            <div class="footer">
                <div>Generado: <?php echo date('d/m/Y H:i'); ?> | Clínica González</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function generarHTMLFacturacion($internado)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Facturación - Internado #<?php echo htmlspecialchars($internado['id_internado']); ?></title>
            <style>
                body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ffc107; padding-bottom: 10px; }
                .hospital-title { color: #1e3c72; font-size: 18px; font-weight: bold; }
                .section { margin-bottom: 15px; }
                .section-title { background-color: #ffc107; color: black; padding: 6px 10px; font-weight: bold; margin-bottom: 8px; }
                .info-row { margin-bottom: 5px; display: flex; }
                .info-label { font-weight: bold; min-width: 120px; color: #1e3c72; }
                .table-cost { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .table-cost th, .table-cost td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .table-cost th { background-color: #f8f9fa; }
                .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="hospital-title">CLÍNICA GONZÁLEZ</div>
                <div style="font-size: 12px;">DETALLE DE FACTURACIÓN</div>
            </div>

            <div class="section">
                <div class="section-title">INFORMACIÓN DE FACTURACIÓN</div>
                <div class="info-row"><div class="info-label">Paciente:</div><div><?php echo htmlspecialchars($internado['nombre_paciente'] ?? 'N/A'); ?></div></div>
                <div class="info-row"><div class="info-label">Internado ID:</div><div>#<?php echo htmlspecialchars($internado['id_internado']); ?></div></div>
                <div class="info-row"><div class="info-label">Período:</div><div><?php echo date('d/m/Y', strtotime($internado['fecha_ingreso'])); ?> - <?php echo $internado['fecha_alta'] ? date('d/m/Y', strtotime($internado['fecha_alta'])) : 'Actual'; ?></div></div>
            </div>

            <div class="section">
                <div class="section-title">DETALLE DE COSTOS</div>
                <table class="table-cost">
                    <tr><th>Concepto</th><th>Descripción</th><th>Costo Estimado</th></tr>
                    <tr><td>Habitación</td><td><?php echo htmlspecialchars($internado['habitacion_numero'] ?? 'N/A'); ?></td><td>Por calcular</td></tr>
                    <tr><td>Tratamientos</td><td>Procedimientos médicos</td><td>Por calcular</td></tr>
                    <tr><td>Medicamentos</td><td>Farmacología</td><td>Por calcular</td></tr>
                    <tr><td>Otros</td><td>Servicios adicionales</td><td>Por calcular</td></tr>
                    <tr style="font-weight: bold; background-color: #f8f9fa;">
                        <td colspan="2">TOTAL ESTIMADO</td>
                        <td>Por calcular</td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <div>Documento informativo - Los costos finales serán confirmados al alta</div>
                <div>Generado: <?php echo date('d/m/Y H:i'); ?> | Clínica González</div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function renderizarPDF($html, $nombreArchivo)
    {
        $dompdf = new Dompdf();
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

// Ejecutar el controlador
$objControl = new controlInternadoPDF();
$objControl->procesarSolicitud();
?>