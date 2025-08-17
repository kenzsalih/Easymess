<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Initialize variables
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');
$month = date('m', strtotime($selected_month));
$year = date('Y', strtotime($selected_month));
$bills = [];
$total_days = date('t', strtotime($selected_month.'-01'));
$success_message = '';
$error_message = '';

// Get costs for selected month
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calculate_bills'])) {
    // Get total variable costs (inventory purchases)
    $variable_costs_sql = "SELECT SUM(cost) as total_variable_cost 
                          FROM inventory_costs 
                          WHERE DATE_FORMAT(purchase_date, '%Y-%m') = ?";
    $variable_costs_stmt = $conn->prepare($variable_costs_sql);
    $variable_costs_stmt->bind_param("s", $selected_month);
    $variable_costs_stmt->execute();
    $variable_costs_result = $variable_costs_stmt->get_result()->fetch_assoc();
    $total_variable_cost = $variable_costs_result['total_variable_cost'] ?? 0;

    // Get total fixed costs
    $fixed_costs_sql = "SELECT SUM(amount) as total_fixed_cost 
                       FROM fixed_costs 
                       WHERE month = ?";
    $fixed_costs_stmt = $conn->prepare($fixed_costs_sql);
    $fixed_costs_stmt->bind_param("s", $selected_month);
    $fixed_costs_stmt->execute();
    $fixed_costs_result = $fixed_costs_stmt->get_result()->fetch_assoc();
    $total_fixed_cost = $fixed_costs_result['total_fixed_cost'] ?? 0;

    // Get residents and their attendance
    $residents_sql = "SELECT u.id, u.name, u.username,
                     ? - COALESCE(SUM(
                         CASE 
                             WHEN ma.mess_cut_status = 'approved' 
                             THEN DATEDIFF(ma.mess_cut_to, ma.mess_cut_from) + 1
                             ELSE 0
                         END
                     ), 0) as days_present
                     FROM users u
                     LEFT JOIN monthly_attendance ma ON u.username = ma.username 
                         AND ma.month = ? AND ma.year = ?
                     WHERE u.role = 'Resident'
                     GROUP BY u.id, u.name, u.username";
    
    $residents_stmt = $conn->prepare($residents_sql);
    $residents_stmt->bind_param("iis", $total_days, $month, $year);
    $residents_stmt->execute();
    $residents_result = $residents_stmt->get_result();

    $total_attendance_days = 0;
    $residents = [];

    while ($row = $residents_result->fetch_assoc()) {
        $residents[] = $row;
        $total_attendance_days += $row['days_present'];
    }

    $total_residents = count($residents);

    if ($total_residents > 0 && $total_attendance_days > 0) {
        // Calculate costs
        $per_day_variable_cost = $total_variable_cost / $total_attendance_days;
        $fixed_cost_per_resident = $total_fixed_cost / $total_residents;

        // Calculate individual bills
        foreach ($residents as $resident) {
            $variable_cost = $per_day_variable_cost * $resident['days_present'];
            $total_cost = $variable_cost + $fixed_cost_per_resident;

            $bills[] = [
                'id' => $resident['id'],
                'name' => $resident['name'],
                'days_present' => $resident['days_present'],
                'fixed_cost' => $fixed_cost_per_resident,
                'variable_cost' => $variable_cost,
                'total_cost' => $total_cost
            ];
        }

        // Save bills to database
        if (isset($_POST['save_bills'])) {
            $conn->begin_transaction();
            try {
                // First delete any existing bills for this month
                $delete_sql = "DELETE FROM mess_bills WHERE month = ? AND year = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("ii", $month, $year);
                $delete_stmt->execute();

                // Prepare insert statement
                $insert_sql = "INSERT INTO mess_bills 
                              (user_id, month, year, days_present, fixed_cost, variable_cost, total_cost, authorized) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);

                if (!$insert_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Get current user (mess secretary) who is authorizing these bills
                $authorized_by = $_SESSION['username'];

                foreach ($bills as $bill) {
                    $authorized = 0; // Default to not authorized (can be changed to 1 if needed)
                    
                    $insert_stmt->bind_param(
                        "iiiidddi",
                        $bill['id'],
                        $month,
                        $year,
                        $bill['days_present'],
                        $bill['fixed_cost'],
                        $bill['variable_cost'],
                        $bill['total_cost'],
                        $authorized
                    );

                    if (!$insert_stmt->execute()) {
                        throw new Exception("Execute failed: " . $insert_stmt->error);
                    }
                }

                $conn->commit();
                $success_message = "Bills saved successfully for " . date('F Y', strtotime($selected_month.'-01'));
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error saving bills: " . $e->getMessage();
                error_log("Error saving bills: " . $e->getMessage());
            }
        }
    } else {
        $error_message = "No residents found for the selected month.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Bill Calculator</title>
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

        .navbar-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .navbar-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .dashboard-container {
            max-width: 800px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-section h2 {
            color: #4e0e3a;
            margin-bottom: 10px;
        }

        .submit-btn {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-right: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .bill-summary {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .bill-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .bill-item {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .bill-item h3 {
            margin-top: 0;
            color: #4e0e3a;
        }

        .bills-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .bills-table th {
            background: #4e0e3a;
            color: white;
            padding: 15px;
            text-align: left;
        }

        .bills-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        .bills-table tr:nth-child(even) {
            background: rgba(0, 0, 0, 0.02);
        }

        .bills-table tr:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .action-button {
            background: #4e0e3a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .action-button:hover {
            background: #48344c;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        input[type="month"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }

        @media print {
            .navbar, .form-section, .action-button {
                display: none;
            }
            
            body::before {
                display: none;
            }
            
            .dashboard-container {
                background: white;
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Mess Bill Calculator</h1>
        <div class="navbar-links">
            <a href="mess_sec_inventory.php">Inventory</a>
            <a href="mess sec.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Calculate Mess Bills</h2>
            <form method="POST">
                <input type="month" name="month" value="<?= htmlspecialchars($selected_month) ?>" required>
                <button type="submit" name="calculate_bills" class="submit-btn">Calculate Bills</button>
                <?php if (!empty($bills)): ?>
                    <button type="submit" name="save_bills" class="submit-btn">Save Bills</button>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($bills)): ?>
            <div class="bill-summary">
                <h2>Monthly Summary - <?= htmlspecialchars(date('F Y', strtotime($selected_month.'-01'))) ?></h2>
                <div class="bill-details">
                    <div class="bill-item">
                        <h3>Total Variable Cost</h3>
                        <p>₹<?= number_format($total_variable_cost, 2) ?></p>
                    </div>
                    <div class="bill-item">
                        <h3>Total Fixed Cost</h3>
                        <p>₹<?= number_format($total_fixed_cost, 2) ?></p>
                    </div>
                    <div class="bill-item">
                        <h3>Total Residents</h3>
                        <p><?= $total_residents ?></p>
                    </div>
                    <div class="bill-item">
                        <h3>Total Attendance Days</h3>
                        <p><?= $total_attendance_days ?></p>
                    </div>
                </div>

                <table class="bills-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Days Present</th>
                            <th>Fixed Cost</th>
                            <th>Variable Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><?= htmlspecialchars($bill['name']) ?></td>
                                <td><?= $bill['days_present'] ?></td>
                                <td>₹<?= number_format($bill['fixed_cost'], 2) ?></td>
                                <td>₹<?= number_format($bill['variable_cost'], 2) ?></td>
                                <td>₹<?= number_format($bill['total_cost'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button class="action-button" onclick="window.print()">Print Bills</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add confirmation before saving bills
        document.querySelector('button[name="save_bills"]')?.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to save these bills? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>