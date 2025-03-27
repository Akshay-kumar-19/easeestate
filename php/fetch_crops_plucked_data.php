<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    $json_error_message = "JSON Decode Error: " . json_last_error_msg();
    error_log($json_error_message . " - Request Body: " . $request_body);
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse JSON data. ' . $json_error_message]);
    exit;
}

$crop_type_id = $data['crop_type'] ?? null;
$plucked_date = $data['plucked_date'] ?? null;

if (empty($crop_type_id) || empty($plucked_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Crop type and plucked date are required.']);
    exit;
}

try {
    $sql = "SELECT
                cp.ripe_kg,
                cp.unripe_kg,
                cp.total_kg,
                cp.kone_count,
                cp.per_kg_rate,
                cp.daily_wage,
                cp.salary_calculation_type,
                w.worker_name,
                ll.lead_name,
                j.job_name
            FROM crops_plucked cp
            JOIN workers w ON cp.worker_id = w.worker_id
            JOIN labour_lead ll ON cp.lead_id = ll.lead_id
            JOIN jobs j ON cp.job_id = j.id
            WHERE cp.user_id = ? AND cp.job_id = ? AND cp.plucked_date = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $crop_type_id, $plucked_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $crops_data = [];
    $total_ripe_kg = 0;
    $total_unripe_kg = 0;
    $total_total_kg = 0;
    $total_pepper_kg = 0;
    $total_areca_kone = 0;


    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rate_or_wage = '';
            if ($row['salary_calculation_type'] == 'per_kg') {
                $rate_or_wage = $row['per_kg_rate'];
            } else if ($row['salary_calculation_type'] == 'daily_wage') {
                $rate_or_wage = $row['daily_wage'];
            }

            $crops_data[] = [
                'worker_name' => $row['worker_name'],
                'lead_name' => $row['lead_name'],
                'ripe_kg' => $row['ripe_kg'],
                'unripe_kg' => $row['unripe_kg'],
                'total_kg' => $row['total_kg'],
                'kone_count' => $row['kone_count'],
                'salary_calculation_type' => $row['salary_calculation_type'],
                'rate_or_wage' => $rate_or_wage,
                'job_name' => $row['job_name'],
            ];
            if ($row['job_name'] == 'cofffee plucking') {
                $total_ripe_kg += $row['ripe_kg'];
                $total_unripe_kg += $row['unripe_kg'];
                $total_total_kg += $row['total_kg'];
            } else if ($row['job_name'] == 'pepper plucking') {
                $total_pepper_kg += $row['total_kg'];
            } else if ($row['job_name'] == 'arreca plucking') {
                $total_areca_kone += $row['kone_count'];
            }
        }
        echo json_encode([
            'status' => 'success',
            'crops_data' => $crops_data,
            'totals' => [
                'coffee' => [
                    'ripe_kg' => number_format($total_ripe_kg, 2),
                    'unripe_kg' => number_format($total_unripe_kg, 2),
                    'total_kg' => number_format($total_total_kg, 2),
                ],
                'pepper' => [
                    'total_kg' => number_format($total_pepper_kg, 2),
                ],
                'areca' => [
                    'total_kone' => number_format($total_areca_kone, 2),
                ],
            ]
        ]);
    } else {
        echo json_encode(['status' => 'no_data', 'message' => 'No data found for selected criteria.']);
    }

} catch (Exception $e) {
    error_log("View Crops Plucked Data Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error fetching crops plucked data: ' . $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
}
?>