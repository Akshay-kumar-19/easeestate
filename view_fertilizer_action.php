<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] === 'fetch_inventory') {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT
                fertilizer_name,
                fertilizer_type,
                SUM(quantity_kg) as total_quantity_kg,
                SUM(quantity_ml) as total_quantity_ml,
                unit
            FROM fertilizer_inventory
            WHERE user_id = ?
            GROUP BY fertilizer_name, fertilizer_type, unit
            ORDER BY fertilizer_name, fertilizer_type";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $output = '';
    while ($row = $result->fetch_assoc()) {
        $output .= '<tr>';
        $output .= '<td>' . htmlspecialchars($row['fertilizer_name']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['fertilizer_type']) . '</td>';
        $quantity = '';
        if ($row['unit'] == 'kg' && $row['total_quantity_kg'] !== null) {
            $quantity = number_format($row['total_quantity_kg'], 2);
        } elseif ($row['unit'] == 'ml' && $row['total_quantity_ml'] !== null) {
            $quantity = number_format($row['total_quantity_ml'], 2);
        } else {
            $quantity = '0';
        }
        $output .= '<td>' . $quantity . '</td>';
        $output .= '<td>' . htmlspecialchars($row['unit']) . '</td>';
        $output .= '</tr>';
    }

    echo $output;

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo "Invalid request or action.";
}
?>