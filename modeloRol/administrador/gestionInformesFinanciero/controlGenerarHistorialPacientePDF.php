<?php
include_once('../../../modelo/BoletaDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlGenerarHistorialPacientePDF
{
    private $objBoletaDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objBoletaDAO = new BoletaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function generarHistorialPDF($idPaciente, $tipoReporte, $fechaInicio = null, $fechaFin = null, $incluirResumen = true, $incluirDetalles = true)
    {
        // Validar datos de entrada
        if (empty($idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("Debe seleccionar un paciente.", "./formGenerarHistorialPacientePDF.php", "error");
            return;
        }

        // Determinar fechas según el tipo de reporte
        list($fechaInicio, $fechaFin) = $this->determinarRangoFechas($tipoReporte, $fechaInicio, $fechaFin);

        // Obtener datos del paciente y boletas
        $objAuxiliar = new EntidadAuxiliarDAO();
        $paciente = $objAuxiliar->obtenerPacientePorId($idPaciente);
        
        if (!$paciente) {
            $this->objMensaje->mensajeSistemaShow("Paciente no encontrado.", "./formGenerarHistorialPacientePDF.php", "error");
            return;
        }

        // Obtener boletas según el rango de fechas
        if ($tipoReporte === 'personalizado' && $fechaInicio && $fechaFin) {
            $boletas = $this->objBoletaDAO->obtenerBoletasPorRangoFechas($idPaciente, $fechaInicio, $fechaFin);
        } else {
            $boletas = $this->objBoletaDAO->obtenerBoletasPorPaciente($idPaciente);
        }

        // Obtener resumen financiero
        $resumen = $this->objBoletaDAO->obtenerResumenFinancieroPaciente($idPaciente);

        // Generar PDF
        $this->crearPDF($paciente, $boletas, $resumen, $tipoReporte, $fechaInicio, $fechaFin, $incluirResumen, $incluirDetalles);
    }

    private function determinarRangoFechas($tipoReporte, $fechaInicio, $fechaFin)
    {
        $hoy = date('Y-m-d');
        
        switch($tipoReporte) {
            case 'ultimos_3_meses':
                $fechaInicio = date('Y-m-d', strtotime('-3 months'));
                $fechaFin = $hoy;
                break;
            case 'ultimo_mes':
                $fechaInicio = date('Y-m-d', strtotime('-1 month'));
                $fechaFin = $hoy;
                break;
            case 'completo':
                $fechaInicio = null;
                $fechaFin = $hoy;
                break;
            case 'personalizado':
                // Usar las fechas proporcionadas
                break;
        }

        return [$fechaInicio, $fechaFin];
    }

    private function crearPDF($paciente, $boletas, $resumen, $tipoReporte, $fechaInicio, $fechaFin, $incluirResumen, $incluirDetalles)
    {
        // Incluir la librería FPDF
        require_once('../../../lib/fpdf/fpdf.php');

        // Crear instancia de PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Encabezado
        $pdf->Cell(0, 10, 'HISTORIAL FINANCIERO - CLINICA MEDICA', 0, 1, 'C');
        $pdf->Ln(5);

        // Información del paciente
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'DATOS DEL PACIENTE', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 6, 'Nombre:', 0, 0);
        $pdf->Cell(0, 6, $paciente['nombre_completo'], 0, 1);
        $pdf->Cell(50, 6, 'DNI:', 0, 0);
        $pdf->Cell(0, 6, $paciente['dni'], 0, 1);
        $pdf->Cell(50, 6, 'Fecha Emision:', 0, 0);
        $pdf->Cell(0, 6, date('d/m/Y H:i:s'), 0, 1);
        
        if ($fechaInicio && $fechaFin) {
            $pdf->Cell(50, 6, 'Periodo:', 0, 0);
            $pdf->Cell(0, 6, date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)), 0, 1);
        }
        
        $pdf->Ln(10);

        // Resumen financiero
        if ($incluirResumen && !empty($resumen)) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'RESUMEN FINANCIERO', 0, 1);
            $pdf->SetFont('Arial', '', 10);

            $totalGeneral = 0;
            foreach ($resumen as $item) {
                $totalGeneral += $item['monto_total'];
                $pdf->Cell(80, 6, 'Metodo de Pago (' . $item['metodo_pago'] . '):', 0, 0);
                $pdf->Cell(40, 6, 'S/ ' . number_format($item['monto_total'], 2), 0, 1);
            }

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(80, 6, 'TOTAL GENERAL:', 0, 0);
            $pdf->Cell(40, 6, 'S/ ' . number_format($totalGeneral, 2), 0, 1);
            $pdf->Ln(10);
        }

        // Detalles de boletas
        if ($incluirDetalles && !empty($boletas)) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'DETALLE DE BOLETAS', 0, 1);
            
            // Encabezado de la tabla
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(25, 6, 'Boleta', 1, 0, 'C');
            $pdf->Cell(25, 6, 'Fecha', 1, 0, 'C');
            $pdf->Cell(60, 6, 'Concepto', 1, 0, 'C');
            $pdf->Cell(30, 6, 'Metodo Pago', 1, 0, 'C');
            $pdf->Cell(25, 6, 'Monto', 1, 0, 'C');
            $pdf->Cell(25, 6, 'Tipo', 1, 1, 'C');

            $pdf->SetFont('Arial', '', 8);
            $totalDetalle = 0;
            foreach ($boletas as $boleta) {
                $pdf->Cell(25, 6, $boleta['numero_boleta'], 1, 0, 'C');
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($boleta['fecha_emision'])), 1, 0, 'C');
                $pdf->Cell(60, 6, substr($boleta['concepto'], 0, 30), 1, 0);
                $pdf->Cell(30, 6, $boleta['metodo_pago'], 1, 0, 'C');
                $pdf->Cell(25, 6, 'S/ ' . number_format($boleta['monto_total'], 2), 1, 0, 'R');
                $pdf->Cell(25, 6, $boleta['tipo'], 1, 1, 'C');
                $totalDetalle += $boleta['monto_total'];
            }

            // Total del detalle
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(140, 6, 'TOTAL DETALLE:', 1, 0, 'R');
            $pdf->Cell(50, 6, 'S/ ' . number_format($totalDetalle, 2), 1, 1, 'R');
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 10, 'No se encontraron boletas para el periodo seleccionado.', 0, 1, 'C');
        }

        // Pie de página
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Pagina ' . $pdf->PageNo(), 0, 0, 'C');

        // Salida del PDF
        $nombreArchivo = 'Historial_Financiero_' . $paciente['dni'] . '_' . date('Ymd_His') . '.pdf';
        $pdf->Output('I', $nombreArchivo);
        exit;
    }
}
?>