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

$summary_type = $data['summary_type'] ?? null;

if (empty($summary_type)) {
    echo json_encode(['status' => 'error', 'message' => 'Summary type is required.']);
    exit;
}

$summary_data = [];

try {
    if ($summary_type === 'yearly') {
        $sql = "SELECT
                    YEAR(cp.plucked_date) AS year,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.ripe_kg ELSE 0 END) AS coffee_ripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.unripe_kg ELSE 0 END) AS coffee_unripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.total_kg ELSE 0 END) AS coffee_total_kg,
                    SUM(CASE WHEN j.job_name = 'pepper plucking' THEN cp.total_kg ELSE 0 END) AS pepper_total_kg,
                    SUM(CASE WHEN j.job_name = 'arreca plucking' THEN cp.kone_count ELSE 0 END) AS areca_total_kone
                FROM crops_plucked cp
                JOIN jobs j ON cp.job_id = j.id
                WHERE cp.user_id = ?
                GROUP BY YEAR(cp.plucked_date)
                ORDER BY YEAR(cp.plucked_date) DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $summary_data[] = [
                'year' => $row['year'],
                'coffee_ripe_kg' => $row['coffee_ripe_kg'],
                'coffee_unripe_kg' => $row['coffee_unripe_kg'],
                'coffee_total_kg' => $row['coffee_total_kg'],
                'pepper_total_kg' => $row['pepper_total_kg'],
                'areca_total_kone' => $row['areca_total_kone'],
            ];
        }


    } else if ($summary_type === 'monthly') {
        $sql = "SELECT
                    DATE_FORMAT(cp.plucked_date, '%Y-%m') AS month,
                    DATE_FORMAT(cp.plucked_date, '%M %Y') AS month_name_year,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.ripe_kg ELSE 0 END) AS coffee_ripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.unripe_kg ELSE 0 END) AS coffee_unripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.total_kg ELSE 0 END) AS coffee_total_kg,
                    SUM(CASE WHEN j.job_name = 'pepper plucking' THEN cp.total_kg ELSE 0 END) AS pepper_total_kg,
                    SUM(CASE WHEN j.job_name = 'arreca plucking' THEN cp.kone_count ELSE 0 END) AS areca_total_kone
                FROM crops_plucked cp
                JOIN jobs j ON cp.job_id = j.id
                WHERE cp.user_id = ?
                GROUP BY DATE_FORMAT(cp.plucked_date, '%Y-%m'), DATE_FORMAT(cp.plucked_date, '%M %Y')
                ORDER BY DATE_FORMAT(cp.plucked_date, '%Y-%m') DESC";


        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $summary_data[] = [
                'month' => $row['month_name_year'],
                'coffee_ripe_kg' => $row['coffee_ripe_kg'],
                'coffee_unripe_kg' => $row['coffee_unripe_kg'],
                'coffee_total_kg' => $row['coffee_total_kg'],
                'pepper_total_kg' => $row['pepper_total_kg'],
                'areca_total_kone' => $row['areca_total_kone'],
            ];
        }


    } else if ($summary_type === 'weekly') {
        $sql = "SELECT
                    YEARWEEK(cp.plucked_date, 3) AS year_week,
                    WEEK(cp.plucked_date, 3) AS week_number,
                    DATE_FORMAT(MIN(cp.plucked_date), '%Y-%m-%d') AS week_start_date,
                    DATE_FORMAT(MAX(cp.plucked_date), '%Y-%m-%d') AS week_end_date,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.ripe_kg ELSE 0 END) AS coffee_ripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.unripe_kg ELSE 0 END) AS coffee_unripe_kg,
                    SUM(CASE WHEN j.job_name = 'cofffee plucking' THEN cp.total_kg ELSE 0 END) AS coffee_total_kg,
                    SUM(CASE WHEN j.job_name = 'pepper plucking' THEN cp.total_kg ELSE 0 END) AS pepper_total_kg,
                    SUM(CASE WHEN j.job_name = 'arreca plucking' THEN cp.kone_count ELSE 0 END) AS areca_total_kone
                FROM crops_plucked cp
                JOIN jobs j ON cp.job_id = j.id
                WHERE cp.user_id = ?
                GROUP BY YEARWEEK(cp.plucked_date, 3), WEEK(cp.plucked_date, 3)
                ORDER BY YEARWEEK(cp.plucked_date, 3) DESC";


        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $summary_data[] = [
                'week_number' => $row['week_number'],
                'week_start_date' => $row['week_start_date'],
                'week_end_date' => $row['week_end_date'],
                'coffee_ripe_kg' => $row['coffee_ripe_kg'],
                'coffee_unripe_kg' => $row['coffee_unripe_kg'],
                'coffee_total_kg' => $row['coffee_total_kg'],
                'pepper_total_kg' => $row['pepper_total_kg'],
                'areca_total_kone' => $row['areca_total_kone'],
            ];
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid summary type.']);
        exit;
    }

    if (empty($summary_data)) {
        echo json_encode(['status' => 'no_data']);
    } else {
        echo json_encode(['status' => 'success', 'summary_data' => $summary_data]);
    }

} catch (Exception $e) {
    error_log("Fetch Crop Summary Data Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error fetching crop summary data: ' . $e->getMessage()]);
} finally {
    if ($stmt) $stmt->close();
    $conn->close();
}
?>