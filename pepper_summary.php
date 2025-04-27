
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';

$summaryType = $_GET['summaryType'] ?? 'total';
$year = $_GET['year'] ?? null;
$month = $_GET['month'] ?? null;

$whereClause = "1=1";
$params = [];

if ($summaryType == 'yearly' && !empty($year)) {
    $whereClause = "YEAR(date_added) = ?";
    $params = [$year];
} else if ($summaryType == 'monthly' && !empty($year) && !empty($month)) {
    $whereClause = "YEAR(date_added) = ? AND MONTH(date_added) = ?";
    $params = [$year, $month];
}


try {
    $sql_totals = "SELECT COUNT(lot_number) as total_lots, SUM(total_bags) as total_bags, SUM(total_weight_kg) as total_weight_kg FROM pepper_inventory_history WHERE " . $whereClause;
    $stmt_totals = $conn->prepare($sql_totals);
    if (!$stmt_totals) throw new Exception("Prepare statement (totals) failed: " . $conn->error);

    if (!empty($params)) {
        if ($summaryType == 'yearly') {
            $stmt_totals->bind_param("s", $params[0]);
        } else if ($summaryType == 'monthly') {
            $stmt_totals->bind_param("ss", $params[0], $params[1]);
        }
    }
    $stmt_totals->execute();
    $result_totals = $stmt_totals->get_result();
    if (!$result_totals) throw new Exception("Summary query (totals) failed: " . $stmt_totals->error);
    $totals = $result_totals->fetch_assoc();
    $stmt_totals->close();


    $summaryByType = [];
    $sql_lots = "SELECT lot_number, date_received, total_bags, total_weight_kg, moisture_level FROM pepper_inventory_history WHERE " . $whereClause . " ORDER BY date_received DESC";
    $stmt_lots = $conn->prepare($sql_lots);
    if (!$stmt_lots) throw new Exception("Prepare statement (lots) failed: " . $conn->error);
    if (!empty($params)) {
        if ($summaryType == 'yearly') {
            $stmt_lots->bind_param("s", $params[0]);
        } else if ($summaryType == 'monthly') {
            $stmt_lots->bind_param("ss", $params[0], $params[1]);
        }
    }
    $stmt_lots->execute();
    $result_lots = $stmt_lots->get_result();
    if (!$result_lots) throw new Exception("Summary query (lots) failed: " . $stmt_lots->error);

    if ($result_lots->num_rows > 0) {
        while ($row = $result_lots->fetch_assoc()) {
            $summaryByType[] = $row;
        }
    }
    $stmt_lots->close();


    if ($totals['total_weight_kg'] == null) {
        $totals['total_weight_kg'] = 0;
    }


    echo json_encode([
        'status' => 'success',
        'totalLots' => $totals['total_lots'] ?? 0,
        'totalBags' => $totals['total_bags'] ?? 0,
        'totalWeightKg' => number_format($totals['total_weight_kg'], 2),
        'summaryByType' => $summaryByType,
        'pepperSummary' => [
            'total_bags' => number_format($totals['total_bags'] ?? 0),
            'total_weight_kg' => number_format($totals['total_weight_kg'], 2)
        ]

    ]);


} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>