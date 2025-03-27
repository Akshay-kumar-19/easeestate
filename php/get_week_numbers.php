<?php
header('Content-Type: application/json');
$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? date('Y');

if (!$month) {
    echo json_encode(['status' => 'error', 'message' => 'Month is required']);
    exit;
}

function getWeekNumbersForMonth($month, $year) {
    $weekNumbers = [];
    $dateTime = new DateTime();
    $dateTime->setDate($year, $month, 1);

    $monthToCheck = $dateTime->format('n');

    while ($dateTime->format('n') == $monthToCheck) {
        $weekNumber = (int)$dateTime->format('W');
        if (!in_array($weekNumber, $weekNumbers)) {
            $weekNumbers[] = $weekNumber;
        }
        $dateTime->modify('+1 week');
    }
    sort($weekNumbers);
    return $weekNumbers;
}

$weekNumbers = getWeekNumbersForMonth($month, $year);

if ($weekNumbers) {
    echo json_encode(['status' => 'success', 'weekNumbers' => $weekNumbers]);
} else {
    echo json_encode(['status' => 'success', 'weekNumbers' => []]);
}
?>