<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
require 'db.php';
require 'salary_calculation.php';

$today = new \DateTime();
$currentDayOfWeek = (int)$today->format('w');
$lastSaturday = new \DateTime();
$daysToSubtractForSat = ($currentDayOfWeek + 1) % 7;
$lastSaturday->modify("-{$daysToSubtractForSat} days");
$week_start_date = $lastSaturday->format('Y-m-d');
$followingFriday = clone $lastSaturday;
$followingFriday->modify('+5 days');
$week_end_date = $followingFriday->format('Y-m-d');

if (!$conn) {
    die("Database connection failed.");
}

$all_salaries_data = getAllWeeklySalaries($conn, $week_start_date, $week_end_date);

$csvData = "Labour Lead,Worker Name,Present Days,Daily Wage Salary,Per KG Salary,Overtime Hours,Overtime Salary,Total Salary\n";

$grandTotalSalary = 0;
$emailBody = "<html><body style='font-family: Arial, sans-serif;'>";
$emailBody .= "<h2>Weekly Salary Report ({$week_start_date} to {$week_end_date})</h2>";

if ($all_salaries_data) {
    foreach ($all_salaries_data as $lead_data) {
        $leadTotalSalary = 0;
        
        $emailBody .= "<h3 style='margin-top: 20px; border-bottom: 2px solid #000;'>Labour Lead: {$lead_data['lead_name']}</h3>";
        $emailBody .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $emailBody .= "<thead><tr style='background-color: #f2f2f2; font-weight: bold; text-align: left;'>";
        $emailBody .= "<th>Worker Name</th><th>Present Days</th><th>Daily Wage Salary</th><th>Per KG Salary</th><th>Overtime Hours</th><th>Overtime Salary</th><th>Total Salary</th></tr></thead><tbody>";

        foreach ($lead_data['worker_salaries'] as $worker_salary) {
            $csvData .= "\"{$lead_data['lead_name']}\",";
            $csvData .= "\"{$worker_salary['worker_name']}\",";
            $csvData .= "{$worker_salary['present_days']},";
            $csvData .= "{$worker_salary['daily_wage_salary']},";
            $csvData .= "{$worker_salary['per_kg_salary']},";
            $csvData .= "{$worker_salary['total_overtime_hours']},";
            $csvData .= "{$worker_salary['overtime_salary']},";
            $csvData .= "{$worker_salary['total_salary']}\n";

            $emailBody .= "<tr>";
            $emailBody .= "<td>{$worker_salary['worker_name']}</td>";
            $emailBody .= "<td>{$worker_salary['present_days']}</td>";
            $emailBody .= "<td>{$worker_salary['daily_wage_salary']}</td>";
            $emailBody .= "<td>{$worker_salary['per_kg_salary']}</td>";
            $emailBody .= "<td>{$worker_salary['total_overtime_hours']}</td>";
            $emailBody .= "<td>{$worker_salary['overtime_salary']}</td>";
            $emailBody .= "<td><strong>{$worker_salary['total_salary']}</strong></td>";
            $emailBody .= "</tr>";

            $leadTotalSalary += (float)$worker_salary['total_salary'];
        }

        $emailBody .= "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
        $emailBody .= "<td colspan='6' style='text-align: right;'>Total Salary for {$lead_data['lead_name']}:</td>";
        $emailBody .= "<td>{$leadTotalSalary}</td>";
        $emailBody .= "</tr>";

        $csvData .= "\"Total for {$lead_data['lead_name']}\",\"\",\"\",\"\",\"\",\"\",\"\",{$leadTotalSalary}\n";

        $grandTotalSalary += $leadTotalSalary;

        $emailBody .= "</tbody></table><br>";
    }

    $emailBody .= "<h3 style='margin-top: 20px; border-top: 2px solid #000;'>Grand Total Salary: " . number_format($grandTotalSalary, 2) . "</h3>";
} else {
    $csvData .= "No salary data found for the period {$week_start_date} to {$week_end_date}.\n";
    $emailBody .= "<p>No salary data found for the selected week.</p>";
}

$emailBody .= "</body></html>";

$tmpFileName = tempnam(sys_get_temp_dir(), 'salary_report_');
file_put_contents($tmpFileName, $csvData);

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'akshayckm03@gmail.com';
    $mail->Password = 'axef xfad atmo snrq';
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('akshayckm03@gmail.com', 'EaseEstate Automated Report');
    $mail->addAddress('akshayckm04@gmail.com');

    $mail->Subject = 'Weekly Salary Report: ' . $week_start_date . ' to ' . $week_end_date;
    $mail->addAttachment($tmpFileName, 'weekly_salary_report_' . $week_start_date . '.csv');
    $mail->isHTML(true);
    $mail->Body = $emailBody;

    $mail->send();
    echo "Salary report email sent successfully.";
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
} finally {
    if (file_exists($tmpFileName)) {
        unlink($tmpFileName);
    }
}

if ($conn instanceof mysqli) {
    $conn->close();
}
