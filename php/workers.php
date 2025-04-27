<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$leads_query = "SELECT lead_id, lead_name FROM labour_lead WHERE user_id = ?";
$stmt = $conn->prepare($leads_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$leads_result = $stmt->get_result();

$selected_lead_id = isset($_GET['lead_id']) ? $_GET['lead_id'] : null;
$workers = [];
if ($selected_lead_id) {
    $workers_query = "SELECT worker_id, worker_name FROM workers WHERE lead_id = ? AND user_id = ?";
    $stmt = $conn->prepare($workers_query);
    $stmt->bind_param("ii", $selected_lead_id, $user_id);
    $stmt->execute();
    $workers_result = $stmt->get_result();
    $workers = $workers_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workers</title>
    <link rel="stylesheet" href="css/worker.css">
    <script src="js/worker.js" defer></script>
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">

</head>
<body>
    <div class="container">
        <h1>Manage Workers</h1>
       

        <form method="GET" action="">
            <label for="lead_id">Select Labour Lead:</label>
            <select name="lead_id" id="lead_id">
                <option value="">-- Select Labour Lead --</option>
                <?php while ($lead = $leads_result->fetch_assoc()): ?>
                    <option value="<?= $lead['lead_id'] ?>" <?= $selected_lead_id == $lead['lead_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lead['lead_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <button id="addWorkerBtn">Add Worker</button>

        <table>
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($selected_lead_id): ?>
                    <?php foreach ($workers as $worker): ?>
                        <tr>
                            <td><?= htmlspecialchars($worker['worker_name']) ?></td>
                            <td>
                                <button class="delete-btn" data-worker-id="<?= $worker['worker_id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Select a labour lead to view workers.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="addWorkerModal" style="display: none;">
        <h2>Add Worker</h2>
        <form id="addWorkerForm">
            <input type="hidden" id="lead_id_hidden" name="lead_id">
            <label for="worker_name">Worker Name:</label>
            <input type="text" id="worker_name" name="worker_name" required>
            <button type="submit">Save</button>
            <button type="button" id="closeModal">Cancel</button>
        </form>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>