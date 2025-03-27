<?php

require 'db.php';

$labourLeads = [];

try {
    $sql_fetch_leads = "SELECT lead_id, lead_name FROM labour_lead";
    $result = $conn->query($sql_fetch_leads);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labourLeads[] = [
                'labour_lead_id' => $row['lead_id'],
                'labour_lead_name' => $row['lead_name']
            ];
        }
    }

    echo json_encode($labourLeads);

} catch (Exception $e) {
    error_log("Error fetching team leads: " . $e->getMessage());
    echo json_encode([]);
} finally {
    $conn->close();
}
?>