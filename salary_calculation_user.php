<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';


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