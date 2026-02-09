<?php
require('conn.php');
require('fpdf.php'); // Asegúrate de tener este archivo

if (!isset($_GET['id'])) die("ID de cotización no especificado.");

$id = $_GET['id'];

// 1. Obtener Datos
$sql = "SELECT q.*, c.tradeName, c.email, p.name as proyecto, u.full_name as vendedor
        FROM quotation q
        JOIN customer c ON q.customer_id = c.id
        JOIN project p ON q.project_id = p.id
        JOIN fos_user u ON q.created_by_id = u.id
        WHERE q.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$head = $stmt->fetch(PDO::FETCH_ASSOC);

$sqlItems = "SELECT d.*, s.messCode 
             FROM quotation_detail d 
             LEFT JOIN service s ON d.service_id = s.id 
             WHERE d.quotation_id = ?";
$stmtItems = $pdo->prepare($sqlItems);
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// 2. Crear PDF
class PDF extends FPDF {
    function Header() {
        // Logo Kepler (Asegúrate de tener la imagen)
        //$this->Image('img/logo_kepler.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80);
        $this->Cell(30, 10, utf8_decode('COTIZACIÓN DE SERVICIOS'), 0, 0, 'C');
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Información del Cliente
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(100, 6, utf8_decode('Cliente: ' . $head['tradeName']), 0, 1);
$pdf->Cell(100, 6, utf8_decode('Atención: ' . $head['email']), 0, 1);
$pdf->Cell(100, 6, utf8_decode('Proyecto: ' . $head['proyecto']), 0, 1);
$pdf->Ln(10);

// Información de la Cotización
$pdf->SetFillColor(26, 42, 58); // Azul Kepler
$pdf->SetTextColor(255);
$pdf->Cell(30, 7, 'Folio', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Vencimiento', 1, 0, 'C', true);
$pdf->Cell(80, 7, 'Ejecutivo', 1, 1, 'C', true);

$pdf->SetTextColor(0);
$pdf->Cell(30, 7, str_pad($head['id'], 6, "0", STR_PAD_LEFT), 1, 0, 'C');
$pdf->Cell(40, 7, date('d/m/Y', strtotime($head['created'])), 1, 0, 'C');
$pdf->Cell(40, 7, date('d/m/Y', strtotime($head['valid_until'])), 1, 0, 'C');
$pdf->Cell(80, 7, utf8_decode($head['vendedor']), 1, 1, 'L');
$pdf->Ln(10);

// Tabla de Partidas
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(20, 7, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(30, 7, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell(90, 7, utf8_decode('Descripción'), 1, 0, 'L', true);
$pdf->Cell(25, 7, 'P. Unit.', 1, 0, 'R', true);
$pdf->Cell(25, 7, 'Total', 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 9);
foreach ($items as $row) {
    $pdf->Cell(20, 6, $row['qty'], 1, 0, 'C');
    $pdf->Cell(30, 6, utf8_decode($row['messCode']), 1, 0, 'C');
    $pdf->Cell(90, 6, utf8_decode(substr($row['description'], 0, 50)), 1, 0, 'L');
    $pdf->Cell(25, 6, '$' . number_format($row['price'], 2), 1, 0, 'R');
    $pdf->Cell(25, 6, '$' . number_format($row['total'], 2), 1, 1, 'R');
}

// Totales
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(140);
$pdf->Cell(25, 7, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($head['total'], 2), 0, 1, 'R');

$iva = $head['total'] * 0.16;
$pdf->Cell(140);
$pdf->Cell(25, 7, 'IVA (16%):', 0, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($iva, 2), 0, 1, 'R');

$pdf->SetTextColor(26, 42, 58);
$pdf->Cell(140);
$pdf->Cell(25, 7, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($head['total'] + $iva, 2), 0, 1, 'R');

$pdf->Output();
?>