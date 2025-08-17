<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Matron') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get pending mess cut requests
$sql = "SELECT id, username, month, year, mess_cut_from, mess_cut_to, mess_cut_status 
        FROM monthly_attendance 
        WHERE mess_cut_status = 'pending' 
        ORDER BY mess_cut_from DESC";
$result = $conn->query($sql);

// Get approved and rejected mess cut requests
$history_sql = "SELECT id, username, month, year, mess_cut_from, mess_cut_to, mess_cut_status,
                DATEDIFF(mess_cut_to, mess_cut_from) + 1 AS days_count
                FROM monthly_attendance 
                WHERE mess_cut_status IN ('approved', 'rejected') 
                ORDER BY mess_cut_from DESC 
                LIMIT 50"; // Limit to recent 50 to avoid performance issues
$history_result = $conn->query($history_sql);

// Debugging
error_log("Pending requests query: $sql");
error_log("Number of pending requests: " . $result->num_rows);
error_log("History requests query: $history_sql");
error_log("Number of history requests: " . $history_result->num_rows);

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_all'])) {
        // Approve all pending requests
        $approve_all_sql = "UPDATE monthly_attendance SET mess_cut_status = 'approved' WHERE mess_cut_status = 'pending'";
        if ($conn->query($approve_all_sql)) {
            $success_message = "All pending requests approved successfully.";
        } else {
            $error_message = "Error approving all requests: " . $conn->error;
        }
    } else {
        $username = $_POST['username'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $mess_cut_from = $_POST['mess_cut_from'];
        $mess_cut_to = $_POST['mess_cut_to'];
        $status = $_POST['action'];
        
        // If ID is available, use it for more precise updates
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $sql = "UPDATE monthly_attendance SET mess_cut_status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $id);
        } else {
            // Fallback to using other fields
            $sql = "UPDATE monthly_attendance SET mess_cut_status = ? 
                    WHERE username = ? AND month = ? AND year = ? AND mess_cut_from = ? AND mess_cut_to = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $status, $username, $month, $year, $mess_cut_from, $mess_cut_to);
        }
        
        // Debugging
        error_log("Update query: $sql");
        error_log("Parameters: $status, $username, $month, $year, $mess_cut_from, $mess_cut_to");
        
        if ($stmt->execute()) {
            $success_message = "Request " . ucfirst($status);
            error_log("Update successful. Rows affected: " . $stmt->affected_rows);
        } else {
            $error_message = "Error updating request: " . $stmt->error;
            error_log("Update failed: " . $stmt->error);
        }
    }
    
    // Refresh the page to update the lists
    header("Location: matron_view_requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Cut Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            position: relative;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 150vh;
            background-image: url('https://imgs.search.brave.com/XAaBZm7wAJa1cNK6eRTjTUkGt-5zsENVfJvBr2YPGiA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly90My5m/dGNkbi5uZXQvanBn/LzA2LzI5LzA3LzIy/LzM2MF9GXzYyOTA3/MjI5NF9sN05nR0VV/dnJRT01KWHFuMTB3/cEY0SEdockRmRVRP/bS5qcGc');
            background-size: cover;
            background-position: center bottom;
            background-repeat: no-repeat;
            filter: blur(5px);
            z-index: -1;
        }

        .navbar {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar h1 {
            margin: 0;
            font-size: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 8px;
            letter-spacing: 1px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .section-title {
            margin-top: 10px;
            margin-bottom: 20px;
            color: #4e0e3a;
            border-bottom: 2px solid #4e0e3a;
            padding-bottom: 5px;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        .requests-table th, .requests-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .requests-table th {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .approve-btn, .reject-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .approve-btn {
            background: #28a745;
            color: white;
        }

        .reject-btn {
            background: #dc3545;
            color: white;
        }

        .approve-btn:hover, .reject-btn:hover {
            transform: translateY(-2px);
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .back-button:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: white;
        }

        .success {
            border-left: 4px solid #28a745;
        }

        .error {
            border-left: 4px solid #dc3545;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-approved {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.4);
        }

        .status-rejected {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.4);
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .filter-controls select {
            padding: 8px;
            border: 1px solid #4e0e3a;
            border-radius: 4px;
        }

        .filter-controls button {
            padding: 8px 16px;
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .no-requests {
            padding: 20px;
            text-align: center;
            background: #f8f8f8;
            border-radius: 4px;
            margin-top: 20px;
        }

        .approve-all-btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .approve-all-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Matron Dashboard - Mess Cut Requests</h1>
    </div>

    <!-- Container for pending requests -->
    <div class="container">
        <h2 class="section-title">Pending Requests</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <!-- Approve All Button -->
            <form method="POST" style="margin-bottom: 20px;">
                <button type="submit" name="approve_all" class="approve-all-btn">Approve All Pending Requests</button>
            </form>

            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Mess Cut From</th>
                        <th>Mess Cut To</th>
                        <th>Days</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // Calculate days
                        $from_date = new DateTime($row['mess_cut_from']);
                        $to_date = new DateTime($row['mess_cut_to']);
                        $interval = $from_date->diff($to_date);
                        $days = $interval->days + 1;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo date('F', mktime(0, 0, 0, $row['month'], 1)); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['mess_cut_from'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['mess_cut_to'])); ?></td>
                            <td><?php echo $days; ?></td>
                            <td class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                                    <input type="hidden" name="month" value="<?php echo $row['month']; ?>">
                                    <input type="hidden" name="year" value="<?php echo $row['year']; ?>">
                                    <input type="hidden" name="mess_cut_from" value="<?php echo $row['mess_cut_from']; ?>">
                                    <input type="hidden" name="mess_cut_to" value="<?php echo $row['mess_cut_to']; ?>">
                                    <button type="submit" name="action" value="approved" class="approve-btn">Approve</button>
                                    <button type="submit" name="action" value="rejected" class="reject-btn">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">
                <p>No pending requests at this time.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- New container for request history -->
    <div class="container">
        <h2 class="section-title">Request History</h2>
        
        <div class="filter-controls">
            <form method="GET" id="filterForm">
                <select name="status" id="statusFilter">
                    <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] == 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <?php if ($history_result->num_rows > 0): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Mess Cut From</th>
                        <th>Mess Cut To</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $history_result->fetch_assoc()): 
                        // Skip if filtering is applied
                        if (isset($_GET['status']) && $_GET['status'] != 'all' && $row['mess_cut_status'] != $_GET['status']) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo date('F', mktime(0, 0, 0, $row['month'], 1)); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['mess_cut_from'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['mess_cut_to'])); ?></td>
                            <td><?php echo $row['days_count']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['mess_cut_status'] == 'approved' ? 'status-approved' : 'status-rejected'; ?>">
                                    <?php echo ucfirst($row['mess_cut_status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-requests">
                <p>No request history available.</p>
            </div>
        <?php endif; ?>

        <a href="matron.php" class="back-button">Back to Dashboard</a>
    </div>

    <script>
        // Auto-submit the form when the status filter changes
        document.getElementById('statusFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
</body>
</html>
