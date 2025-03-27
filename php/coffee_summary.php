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
    $sql_totals = "SELECT COUNT(*) as total_lots, SUM(total_bags) as total_bags, SUM(total_weight_kg) as total_weight_kg FROM coffee_inventory_history WHERE " . $whereClause;
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


    $sql_by_type = "SELECT coffee_type, SUM(total_bags) as total_bags, SUM(total_weight_kg) as total_weight_kg FROM coffee_inventory_history WHERE " . $whereClause . " GROUP BY coffee_type";
    $stmt_by_type = $conn->prepare($sql_by_type);
    if (!$stmt_by_type) throw new Exception("Prepare statement (by type) failed: " . $conn->error);
    if (!empty($params)) {
        if ($summaryType == 'yearly') {
            $stmt_by_type->bind_param("s", $params[0]);
        } else if ($summaryType == 'monthly') {
            $stmt_by_type->bind_param("ss", $params[0], $params[1]);
        }
    }
    $stmt_by_type->execute();
    $result_by_type = $stmt_by_type->get_result();

    if (!$result_by_type) throw new Exception("Summary query (by type) failed: " . $stmt_by_type->error);

    $summaryByType = [];
    $parchmentSummary = ['total_bags' => 0, 'total_weight_kg' => 0];
    $cherrySummary = ['total_bags' => 0, 'total_weight_kg' => 0];


    while ($row = $result_by_type->fetch_assoc()) {
        $summaryByType[] = $row;

        if ($row['coffee_type'] == 'Parchment') {
            $parchmentSummary['total_bags'] += $row['total_bags'];
            $parchmentSummary['total_weight_kg'] += $row['total_weight_kg'];
        } else if ($row['coffee_type'] == 'Cherry') {
            $cherrySummary['total_bags'] += $row['total_bags'];
            $cherrySummary['total_weight_kg'] += $row['total_weight_kg'];
        }
    }
    $stmt_by_type->close();


    echo json_encode([
        'status' => 'success',
        'totalLots' => $totals['total_lots'],
        'totalBags' => $totals['total_bags'],
        'totalWeightKg' => number_format($totals['total_weight_kg'], 2),
        'summaryByType' => $summaryByType,
        'parchmentSummary' => [
            'total_bags' => number_format($parchmentSummary['total_bags']),
            'total_weight_kg' => number_format($parchmentSummary['total_weight_kg'], 2)
        ],
        'cherrySummary' => [
            'total_bags' => number_format($cherrySummary['total_bags']),
            'total_weight_kg' => number_format($cherrySummary['total_weight_kg'], 2)
        ]
    ]);


} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>