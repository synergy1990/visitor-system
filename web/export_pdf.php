<?php

require "db.php";
require "lib/fpdf.php";

if (!isset($_SESSION['user'])) {
    exit;
}


/* =========================
   UTF-8 → FPDF kompatibel
========================= */

function normalize_text($text)
{

    $search  = ['ä','ö','ü','Ä','Ö','Ü','ß'];
    $replace = ['ae','oe','ue','Ae','Oe','Ue','ss'];

    $text = str_replace($search, $replace, $text);

    // Restliche Sonderzeichen absichern
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
}


/* =========================
   PDF Erweiterung (Circle)
========================= */

class PDF extends FPDF
{
    public function Circle($x, $y, $r, $style = 'D')
    {
        $k = $this->k;
        $h = $this->h;

        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }

        $MyArc = 4 / 3 * (sqrt(2) - 1);

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($h - $y) * $k));

        $this->_Arc($x + $r, $y - $r * $MyArc, $x + $r * $MyArc, $y - $r, $x, $y - $r);
        $this->_Arc($x - $r * $MyArc, $y - $r, $x - $r, $y - $r * $MyArc, $x - $r, $y);
        $this->_Arc($x - $r, $y + $r * $MyArc, $x - $r * $MyArc, $y + $r, $x, $y + $r);
        $this->_Arc($x + $r * $MyArc, $y + $r, $x + $r, $y + $r * $MyArc, $x + $r, $y);

        $this->_out($op);
    }

    public function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

}


/* =========================
   Daten laden
========================= */

$data = $db->query("
SELECT
firstname,
lastname,
company,
contact,
persons,
checkin,
checkout,
status
FROM visitors
ORDER BY
CASE status WHEN 'present' THEN 0 ELSE 1 END,
lastname,
firstname,
company,
checkin
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   Statistik (nur heute)
========================= */

$total = 0;
$present = 0;

foreach ($data as $v) {

    if (date("Y-m-d", strtotime($v['checkin'])) == date("Y-m-d")) {

        $total += (int)$v['persons'];

        if ($v['status'] == "present") {
            $present += (int)$v['persons'];
        }
    }
}

$absent = $total - $present;


/* =========================
   PDF erzeugen
========================= */

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);


/* Logo */

$pdf->Image('firmenlogo.png', 10, 8, 40);


/* Titel */

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, normalize_text('Besucherliste'), 0, 1, 'C');


/* Datum */

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 8, normalize_text("Erstellt am: ".date("d.m.Y H:i")), 0, 1, 'R');

$pdf->Ln(3);


/* Statistik */

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(
    0,
    8,
    normalize_text("Gesamtbesucher: $total | Anwesend: $present | Abwesend: $absent"),
    0,
    1,
    'C'
);

$pdf->Ln(4);


/* =========================
   Daten vorbereiten
========================= */

$pdf->SetFont('Arial', '', 9);

$rows = [];

foreach ($data as $v) {

    $rows[] = [
        "name" => normalize_text($v['lastname'].", ".$v['firstname']),
        "company" => normalize_text($v['company']),
        "persons" => $v['persons'],
        "contact" => normalize_text($v['contact']),
        "checkin" => normalize_text($v['checkin']),
        "checkout" => normalize_text($v['checkout'] ?? "-"),
        "status" => $v['status']
    ];
}


/* =========================
   Spaltenbreiten
========================= */

$widths = [
    "status" => 12,
    "name" => $pdf->GetStringWidth("Nachname, Vorname") + 6,
    "company" => $pdf->GetStringWidth("Firma") + 6,
    "persons" => $pdf->GetStringWidth("Anzahl Personen.") + 6,
    "contact" => $pdf->GetStringWidth("Anpsrechpartner") + 6,
    "checkin" => $pdf->GetStringWidth("Check-In") + 6,
    "checkout" => $pdf->GetStringWidth("Check-Out") + 6
];

foreach ($rows as $r) {

    $widths["name"] = max($widths["name"], $pdf->GetStringWidth($r["name"]) + 6);
    $widths["company"] = max($widths["company"], $pdf->GetStringWidth($r["company"]) + 6);
    $widths["contact"] = max($widths["contact"], $pdf->GetStringWidth($r["contact"]) + 6);
    $widths["checkin"] = max($widths["checkin"], $pdf->GetStringWidth($r["checkin"]) + 6);
    $widths["checkout"] = max($widths["checkout"], $pdf->GetStringWidth($r["checkout"]) + 6);
}


/* Skalierung */

$pageWidth = $pdf->GetPageWidth() - 20;
$currentWidth = array_sum($widths);
$scale = $pageWidth / $currentWidth;

foreach ($widths as $k => $v) {
    $widths[$k] = $v * $scale;
}


/* =========================
   Header
========================= */

$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell($widths["status"], 8, "Status", 1);
$pdf->Cell($widths["name"], 8, normalize_text("Nachname, Vorname"), 1);
$pdf->Cell($widths["company"], 8, "Firma", 1);
$pdf->Cell($widths["persons"], 8, "Anzahl Personen", 1);
$pdf->Cell($widths["contact"], 8, "Ansprechpartner", 1);
$pdf->Cell($widths["checkin"], 8, "Check-In", 1);
$pdf->Cell($widths["checkout"], 8, "Check-Out", 1);

$pdf->Ln();


/* =========================
   Inhalte
========================= */

$fill = false;

foreach ($rows as $r) {

    $bg = $fill ? 240 : 255;
    $pdf->SetFillColor($bg, $bg, $bg);

    $x = $pdf->GetX();
    $y = $pdf->GetY();

    /* Status */

    $pdf->Cell($widths["status"], 7, '', 1, 0, 'C', true);

    $cx = $x + ($widths["status"] / 2);
    $cy = $y + 3.5;

    if ($r["status"] == "present") {
        $pdf->SetFillColor(0, 180, 0);
    } else {
        $pdf->SetFillColor(200, 0, 0);
    }

    $pdf->Circle($cx, $cy, 2, 'F');

    $pdf->SetFillColor($bg, $bg, $bg);

    /* Daten */

    $pdf->Cell($widths["name"], 7, $r["name"], 1, 0, 'L', true);
    $pdf->Cell($widths["company"], 7, $r["company"], 1, 0, 'L', true);
    $pdf->Cell($widths["persons"], 7, $r["persons"], 1, 0, 'C', true);
    $pdf->Cell($widths["contact"],7,$r["contact"],1,0,'L',true);
    $pdf->Cell($widths["checkin"],7,$r["checkin"],1,0,'L',true);
    $pdf->Cell($widths["checkout"],7,$r["checkout"],1,0,'L',true);

    $pdf->Ln();

    $fill = !$fill;
}


/* =========================
   Output
========================= */

$pdf->Output("I","Besucherliste.pdf");
