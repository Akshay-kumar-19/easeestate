
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php'; // Database connection

$summaryType = $_GET['summaryType'] ?? 'total'; // Default to total summary if not provided
$year = $_GET['year'] ?? null;
$month = $_GET['month'] ?? null;

$whereClause = "1=1"; // Default: no filter (for total summary)
$params = []; // Parameters for prepared statement

if ($summaryType == 'yearly' && !empty($year)) {
    $whereClause = "YEAR(date_added) = ?";
    $params = [$year];
} else if ($summaryType == 'monthly' && !empty($year) && !empty($month)) {
    $whereClause = "YEAR(date_added) = ? AND MONTH(date_added) = ?";
    $params = [$year, $month];
}


try {
    // 1. Calculate overall totals with filtering - from pepper_inventory_history
    $sql_totals = "SELECT COUNT(lot_number) as total_lots, SUM(total_bags) as total_bags, SUM(total_weight_kg) as total_weight_kg FROM pepper_inventory_history WHERE " . $whereClause; // Query from pepper_inventory_history
    $stmt_totals = $conn->prepare($sql_totals);
    if (!$stmt_totals) throw new Exception("Prepare statement (totals) failed: " . $conn->error);

    if (!empty($params)) {
        if ($summaryType == 'yearly') {
            $stmt_totals->bind_param("s", $params[0]); // 's' for year (string)
        } else if ($summaryType == 'monthly') {
            $stmt_totals->bind_param("ss", $params[0], $params[1]); // 'ss' for year and month (strings)
        }
    }
    $stmt_totals->execute();
    $result_totals = $stmt_totals->get_result();
    if (!$result_totals) throw new Exception("Summary query (totals) failed: " . $stmt_totals->error);
    $totals = $result_totals->fetch_assoc();
    $stmt_totals->close();


    // 2. Fetch individual pepper lot records for the summary table - Updated Query
    $summaryByType = []; // Will now store pepper lot records
    $sql_lots = "SELECT lot_number, date_received, total_bags, total_weight_kg, moisture_level FROM pepper_inventory_history WHERE " . $whereClause . " ORDER BY date_received DESC"; // Fetch lot details
    $stmt_lots = $conn->prepare($sql_lots);
    if (!$stmt_lots) throw new Exception("Prepare statement (lots) failed: " . $conn->error);
    if (!empty($params)) {
        if ($summaryType == 'yearly') {
            $stmt_lots->bind_param("s", $params[0]); // 's' for year
        } else if ($summaryType == 'monthly') {
            $stmt_lots->bind_param("ss", $params[0], $params[1]); // 'ss' for year and month
        }
    }
    $stmt_lots->execute();
    $result_lots = $stmt_lots->get_result();
    if (!$result_lots) throw new Exception("Summary query (lots) failed: " . $stmt_lots->error);

    if ($result_lots->num_rows > 0) {
        while ($row = $result_lots->fetch_assoc()) {
            $summaryByType[] = $row; // Store pepper lot records in summaryByType
        }
    }
    $stmt_lots->close();


    if ($totals['total_weight_kg'] == null) {
        $totals['total_weight_kg'] = 0;
    }


    echo json_encode([
        'status' => 'success',
        'totalLots' => $totals['total_lots'] ?? 0, // Handle potential null values
        'totalBags' => $totals['total_bags'] ?? 0,
        'totalWeightKg' => number_format($totals['total_weight_kg'], 2), // Format weight to 2 decimal places
        'summaryByType' => $summaryByType, // Now sending pepper lot records here
        'pepperSummary' => [ // Added generic pepper summary instead of type-specific
            'total_bags' => number_format($totals['total_bags'] ?? 0),
            'total_weight_kg' => number_format($totals['total_weight_kg'], 2)
        ]

    ]);


} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>