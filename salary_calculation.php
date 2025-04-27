<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';

function calculateWeeklySalaryForWorker($conn, $worker_id, $lead_id, $week_start_date, $week_end_date) {
    $salary_details = [
        'worker_name' => '',
        'present_days' => 0,
        'total_overtime_hours' => 0,
        'daily_wage_salary' => 0,
        'per_kg_salary' => 0,
        'overtime_salary' => 0,
        'total_salary' => 0,
    ];

    $worker_sql = "SELECT worker_name FROM workers WHERE worker_id =?";
    $worker_stmt = $conn->prepare($worker_sql);
    $worker_stmt->bind_param("i", $worker_id);
    $worker_stmt->execute();
    $worker_result = $worker_stmt->get_result();
    if ($worker_row = $worker_result->fetch_assoc()) {
        $salary_details['worker_name'] = htmlspecialchars($worker_row['worker_name']);
    } else {
        return null;
    }

    $attendance_sql = "SELECT attendance.*, jobs.daily_wage, jobs.per_kg_rate FROM attendance
                                                        INNER JOIN jobs ON attendance.job_id = jobs.id
                                                        WHERE attendance.worker_id =? AND attendance.lead_id =?
                                                        AND attendance.date >=? AND attendance.date <=? AND attendance.present = 1";
    $attendance_stmt = $conn->prepare($attendance_sql);
    $attendance_stmt->bind_param("iiss", $worker_id, $lead_id, $week_start_date, $week_end_date);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    $present_days = 0;
    $daily_wage_rate = 0;
    $per_kg_rate_value = 0;
    $is_per_kg_job = false;

    while ($attendance_row = $attendance_result->fetch_assoc()) {
        $present_days++;
        if ($daily_wage_rate == 0 && $per_kg_rate_value == 0) {
            $daily_wage_rate = $attendance_row['daily_wage'];
            $per_kg_rate_value = $attendance_row['per_kg_rate'];
            if ($per_kg_rate_value > 0) {
                $is_per_kg_job = true;
            }
        }
    }
    $salary_details['present_days'] = $present_days;

    if ($is_per_kg_job) {
        $salary_details['daily_wage_salary'] = 0;
        $crops_plucked_sql = "SELECT SUM(total_kg) AS total_kg_plucked FROM crops_plucked
                                                                                WHERE worker_id = ? AND lead_id = ?
                                                                                AND plucked_date >= ? AND plucked_date <= ?";
        $crops_plucked_stmt = $conn->prepare($crops_plucked_sql);
        $crops_plucked_stmt->bind_param("iiss", $worker_id, $lead_id, $week_start_date, $week_end_date);
        $crops_plucked_stmt->execute();
        $crops_plucked_result = $crops_plucked_stmt->get_result();
        if ($crops_plucked_row = $crops_plucked_result->fetch_assoc()) {
            $total_kg_plucked = $crops_plucked_row['total_kg_plucked'] ?: 0;
            $salary_details['per_kg_salary'] = $total_kg_plucked * $per_kg_rate_value;
        }
    } else {
        $salary_details['daily_wage_salary'] = $present_days * $daily_wage_rate;
        $salary_details['per_kg_salary'] = 0;
    }


    $overtime_sql = "SELECT SUM(overtime_hours) AS total_overtime FROM overtime
                                                        INNER JOIN jobs ON overtime.job_id = jobs.id
                                                        WHERE overtime.worker_id = ? AND overtime.lead_id = ?
                                                        AND overtime.date >= ? AND overtime.date <= ?";

    $overtime_stmt = $conn->prepare($overtime_sql);
    $overtime_stmt->bind_param("iiss", $worker_id, $lead_id, $week_start_date, $week_end_date);
    $overtime_stmt->execute();
    $overtime_result = $overtime_stmt->get_result();
    $total_overtime_hours = 0;
    $overtime_hourly_rate = 0;

    if ($overtime_row = $overtime_result->fetch_assoc()) {
        $total_overtime_hours = $overtime_row['total_overtime'] ?: 0;
        $salary_details['total_overtime_hours'] = $total_overtime_hours;

        $job_id_from_overtime = 0;
        $first_overtime_sql = "SELECT job_id FROM overtime WHERE worker_id = ? AND lead_id = ? AND date >= ? AND date <= ? LIMIT 1";
        $first_overtime_stmt = $conn->prepare($first_overtime_sql);
        $first_overtime_stmt->bind_param("iiss", $worker_id, $lead_id, $week_start_date, $week_end_date);
        $first_overtime_stmt->execute();
        $first_overtime_result = $first_overtime_stmt->get_result();
        if ($first_overtime_row = $first_overtime_result->fetch_assoc()) {
            $job_id_from_overtime = $first_overtime_row['job_id'];
        }


        if ($job_id_from_overtime > 0) {
            $job_rate_sql = "SELECT overtime_hourly_rate FROM jobs WHERE id = ?";
            $job_rate_stmt = $conn->prepare($job_rate_sql);
            $job_rate_stmt->bind_param("i", $job_id_from_overtime);
            $job_rate_stmt->execute();
            $job_rate_result = $job_rate_stmt->get_result();
            if ($job_rate_row = $job_rate_result->fetch_assoc()) {
                $overtime_hourly_rate = $job_rate_row['overtime_hourly_rate'];
            }
        }
         if ($overtime_hourly_rate == 0) {
            $job_id_from_attendance = 0;
            $first_attendance_sql = "SELECT job_id FROM attendance WHERE worker_id = ? AND lead_id = ? AND date >= ? AND date <= ? LIMIT 1";
            $first_attendance_stmt = $conn->prepare($first_attendance_sql);
            $first_attendance_stmt->bind_param("iiss", $worker_id, $lead_id, $week_start_date, $week_end_date);
            $first_attendance_stmt->execute();
            $first_attendance_result = $first_attendance_stmt->get_result();
            if ($first_attendance_row = $first_attendance_result->fetch_assoc()) {
                $job_id_from_attendance = $first_attendance_row['job_id'];
            }
             if ($job_id_from_attendance > 0) {
                $job_rate_sql = "SELECT overtime_hourly_rate FROM jobs WHERE id = ?";
                $job_rate_stmt = $conn->prepare($job_rate_sql);
                $job_rate_stmt->bind_param("i", $job_id_from_attendance);
                $job_rate_stmt->execute();
                $job_rate_result = $job_rate_stmt->get_result();
                if ($job_rate_row = $job_rate_result->fetch_assoc()) {
                    $overtime_hourly_rate = $job_rate_row['overtime_hourly_rate'];
                }
            }
        }


        $salary_details['overtime_salary'] = $total_overtime_hours * $overtime_hourly_rate;
    }


    $salary_details['total_salary'] = $salary_details['daily_wage_salary'] + $salary_details['per_kg_salary'] + $salary_details['overtime_salary'];

    return $salary_details;
}


function getWeeklySalariesByLead($conn, $lead_id, $week_start_date, $week_end_date) {
    $lead_salary_data = [
        'lead_name' => '',
        'worker_salaries' => [],
        'totals' => [
            'total_present_days' => 0,
            'total_daily_wage_salary' => 0,
            'total_per_kg_salary' => 0,
            'total_overtime_hours' => 0,
            'total_overtime_salary' => 0,
            'grand_total_salary' => 0,
        ],
    ];

    $lead_name_sql = "SELECT lead_name FROM labour_lead WHERE lead_id =?";
    $lead_name_stmt = $conn->prepare($lead_name_sql);
    $lead_name_stmt->bind_param("i", $lead_id);
    $lead_name_stmt->execute();
    $lead_name_result = $lead_name_stmt->get_result();
    if ($lead_name_row = $lead_name_result->fetch_assoc()) {
        $lead_salary_data['lead_name'] = htmlspecialchars($lead_name_row['lead_name']);
    }

    $workers_sql = "SELECT worker_id FROM workers WHERE lead_id =?";
    $workers_stmt = $conn->prepare($workers_sql);
    $workers_stmt->bind_param("i", $lead_id);
    $workers_stmt->execute();
    $workers_result = $workers_stmt->get_result();

    while ($worker_row = $workers_result->fetch_assoc()) {
        $worker_id = $worker_row['worker_id'];
        $worker_salary = calculateWeeklySalaryForWorker($conn, $worker_id, $lead_id, $week_start_date, $week_end_date);
        if ($worker_salary) {
            $lead_salary_data['worker_salaries'][] = $worker_salary;

            $lead_salary_data['totals']['total_present_days'] += $worker_salary['present_days'];
            $lead_salary_data['totals']['total_daily_wage_salary'] += $worker_salary['daily_wage_salary'];
            $lead_salary_data['totals']['total_per_kg_salary'] += $worker_salary['per_kg_salary'];
            $lead_salary_data['totals']['total_overtime_hours'] += $worker_salary['total_overtime_hours'];
            $lead_salary_data['totals']['total_overtime_salary'] += $worker_salary['overtime_salary'];
            $lead_salary_data['totals']['grand_total_salary'] += $worker_salary['total_salary'];
        }
    }

    return $lead_salary_data;
}

function getAllWeeklySalaries($conn, $week_start_date, $week_end_date) {
    $all_leads_salary_data = [];

    $leads_sql = "SELECT lead_id FROM labour_lead";
    $leads_result = $conn->query($leads_sql);

    if ($leads_result) {
        while ($lead_row = $leads_result->fetch_assoc()) {
            $lead_id = $lead_row['lead_id'];
            $lead_weekly_salary_data = getWeeklySalariesByLead($conn, $lead_id, $week_start_date, $week_end_date);
            $all_leads_salary_data[] = $lead_weekly_salary_data;
        }
    }

    return $all_leads_salary_data;
}


function outputCsv($all_salaries_data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="weekly_salary_report.csv"');
    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Labour Lead',
        'Worker Name',
        'Present Days',
        'Daily Wage Salary (₹)',
        'Per KG Salary (₹)',
        'Overtime Hours',
        'Overtime Salary (₹)',
        'Total Salary (₹)'
    ]);

    foreach ($all_salaries_data as $lead_data) {
        if (isset($lead_data['worker_salaries'])) {
            foreach ($lead_data['worker_salaries'] as $worker_salary) {
                fputcsv($output, [
                    $lead_data['lead_name'],
                    $worker_salary['worker_name'],
                    $worker_salary['present_days'],
                    $worker_salary['daily_wage_salary'],
                    $worker_salary['per_kg_salary'],
                    $worker_salary['total_overtime_hours'],
                    $worker_salary['overtime_salary'],
                    $worker_salary['total_salary']
                ]);
            }
            fputcsv($output, [
                $lead_data['lead_name'] . ' Totals',
                '',
                $lead_data['totals']['total_present_days'],
                $lead_data['totals']['total_daily_wage_salary'],
                $lead_data['totals']['total_per_kg_salary'],
                $lead_data['totals']['total_overtime_hours'],
                $lead_data['totals']['total_overtime_salary'],
                $lead_data['totals']['grand_total_salary']
            ]);
            fputcsv($output, []);
        }
    }

    fclose($output);
}


if (isset($_GET['week_start']) && isset($_GET['week_end'])) {
    $week_start_date = $_GET['week_start'];
    $week_end_date = $_GET['week_end'];

    $all_salaries_data = getAllWeeklySalaries($conn, $week_start_date, $week_end_date);

    if (isset($_GET['report_format']) && $_GET['report_format'] === 'csv') {
        outputCsv($all_salaries_data);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode($all_salaries_data);
    }
}
?>