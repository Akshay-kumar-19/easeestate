<?php
# get_team_leads.php - Backend to Fetch Team Lead Names (using user's table) - File renamed from labour_lead_fetch.php

require 'db.php'; // Database connection

$labourLeads = []; // Variable name remains labourLeads for backend consistency

try {
    $sql_fetch_leads = "SELECT lead_id, lead_name FROM labour_lead"; // Use 'lead_id' and 'lead_name' - table and column names remain 'labour_lead'
    $result = $conn->query($sql_fetch_leads);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labourLeads[] = [
                'labour_lead_id' => $row['lead_id'], // Key name remains labour_lead_id for backend consistency
                'labour_lead_name' => $row['lead_name'] // Key name remains labour_lead_name for backend consistency
            ];
        }
    }

    echo json_encode($labourLeads);

} catch (Exception $e) {
    error_log("Error fetching team leads: " . $e->getMessage()); // Updated log message - team leads
    echo json_encode([]); // Return empty array in case of error
} finally {
    $conn->close();
}
?>