<?php
session_start();
require 'db.php';
require 'salary_calculation.php';
require_once 'C:\xampp\htdocs\easeestate\tcpdf\tcpdf\tcpdf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$week_start_date = $_GET['week_start'] ?? null;
$week_end_date = $_GET['week_end'] ?? null;

if (!$week_start_date || !$week_end_date) {
    echo "Week start and end dates are required.";
    exit;
}

$all_salaries_data = getAllWeeklySalaries($conn, $week_start_date, $week_end_date);

if (empty($all_salaries_data)) {
    echo "No salary data found for the selected week.";
    exit;
}

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('EaseEstate');
$pdf->SetTitle('Weekly Labour Salary Report');
$pdf->SetSubject('Weekly Salary Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('dejavusans', '', 10, '', true);


$pdf->SetFontSize(16);
$pdf->Cell(0, 15, 'Weekly Labour Salary Report', 0, 1, 'C');
$pdf->Ln(5);


$pdf->SetFontSize(12);
$pdf->Write(0, "Week: " . date('d M Y', strtotime($week_start_date)) . " to " . date('d M Y', strtotime($week_end_date)) ."\n\n");


$pdf->SetFontSize(10);

$grand_total_all_leads_pdf = 0;

foreach ($all_salaries_data as $lead_data) {
    if (!empty($lead_data['worker_salaries'])) {
        $pdf->SetFontSize(14);
        $pdf->Cell(0, 10, 'Labour Lead: ' . $lead_data['lead_name'], 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('dejavusans', 'B', 9, '', true);
        $pdf->Cell(40, 7, 'Worker Name', 1, 0, 'L', 1);
        $pdf->Cell(20, 7, 'Days', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Daily Wage', 1, 0, 'R', 1);
        $pdf->Cell(30, 7, 'Per KG Salary', 1, 0, 'R', 1);
        $pdf->Cell(25, 7, 'OT Hours', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'OT Salary', 1, 0, 'R', 1);
        $pdf->Cell(35, 7, 'Total Salary', 1, 1, 'R', 1);

        $pdf->SetFont('dejavusans', '', 9, '', true);
        foreach ($lead_data['worker_salaries'] as $worker_salary) {
            $pdf->Cell(40, 6, $worker_salary['worker_name'], 1, 0, 'L');
            $pdf->Cell(20, 6, $worker_salary['present_days'], 1, 0, 'C');
            $pdf->Cell(30, 6, '₹' . number_format($worker_salary['daily_wage_salary'], 2), 1, 0, 'R');
            $pdf->Cell(30, 6, '₹' . number_format($worker_salary['per_kg_salary'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, number_format($worker_salary['total_overtime_hours'], 2), 1, 0, 'C');
            $pdf->Cell(30, 6, '₹' . number_format($worker_salary['overtime_salary'], 2), 1, 0, 'R');
            $pdf->Cell(35, 6, '₹' . number_format($worker_salary['total_salary'], 2), 1, 1, 'R');
        }

        $pdf->SetFont('dejavusans', 'B', 9, '', true);
        $pdf->Cell(40, 7, 'Total for ' . $lead_data['lead_name'], 1, 0, 'L');
        $pdf->Cell(20, 7, $lead_data['totals']['total_present_days'], 1, 0, 'C');
        $pdf->Cell(30, 7, '₹' . number_format($lead_data['totals']['total_daily_wage_salary'], 2), 1, 0, 'R');
        $pdf->Cell(30, 7, '₹' . number_format($lead_data['totals']['total_per_kg_salary'], 2), 1, 0, 'R');
        $pdf->Cell(25, 7, number_format($lead_data['totals']['total_overtime_hours'], 2), 1, 0, 'C');
        $pdf->Cell(30, 7, '₹' . number_format($lead_data['totals']['total_overtime_salary'], 2), 1, 0, 'R');
        $pdf->Cell(35, 7, '₹' . number_format($lead_data['totals']['grand_total_salary'], 2), 1, 1, 'R');
        $pdf->Ln(5);

        $grand_total_all_leads_pdf += $lead_data['totals']['grand_total_salary'];
    }
}

$pdf->SetFontSize(14);
$pdf->Cell(0, 10, 'Grand Total Salary for All Leads: ₹' . number_format($grand_total_all_leads_pdf, 2), 0, 1, 'L');


$pdf->Output('weekly_salary_report.pdf', 'D');
?>