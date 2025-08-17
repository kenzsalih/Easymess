<?php
session_start();

// Check if user is logged in and is a Mess Secretary
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get selected month and year or default to current
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : date('n');
$selectedYear = isset($_POST['year']) ? $_POST['year'] : date('Y');

// Fetch users who haven't paid for the selected month
$sql = "SELECT u.username 
        FROM users u 
        LEFT JOIN mess_payments m ON u.username = m.username 
        AND m.month = ? AND m.year = ?
        WHERE u.role = 'Resident' 
        AND m.id IS NULL 
        ORDER BY u.username";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $selectedMonth, $selectedYear);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments List</title>
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
        .month-display {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 5px;
        }
        .pending-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .pending-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .pending-table th, .pending-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .pending-table th {
            background-color: #4e0e3a;
            color: white;
        }
        .pending-table tr:hover {
            background-color: rgba(78, 14, 58, 0.1);
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0e3a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: rgba(78, 14, 58, 0.1);
            border-radius: 5px;
        }
        .month-selector {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .month-selector form {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
        }
        .month-selector select, .month-selector input[type="submit"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .month-selector input[type="submit"] {
            background-color: #4e0e3a;
            color: white;
            border: none;
            padding: 8px 20px;
            cursor: pointer;
        }
        .month-selector input[type="submit"]:hover {
            background-color: #48344c;
        }
    </style>
</head>
<body>
    <div>
        <nav class="navbar">
        <h1>Pending Payments List</h1>
        </nav>
    </div>

    <div class="month-selector">
        <form method="POST">
            <select name="month" required>
                <?php
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 
                    4 => 'April', 5 => 'May', 6 => 'June',
                    7 => 'July', 8 => 'August', 9 => 'September',
                    10 => 'October', 11 => 'November', 12 => 'December'
                ];
                foreach ($months as $num => $name) {
                    $selected = ($num == $selectedMonth) ? 'selected' : '';
                    echo "<option value='$num' $selected>$name</option>";
                }
                ?>
            </select>
            <select name="year" required>
                <?php
                $currentYear = date('Y');
                for ($year = $currentYear; $year >= $currentYear - 2; $year--) {
                    $selected = ($year == $selectedYear) ? 'selected' : '';
                    echo "<option value='$year' $selected>$year</option>";
                }
                ?>
            </select>
            <input type="submit" value="Show Pending List">
        </form>
    </div>

    <div class="pending-container">
        <div class="summary">
            <h3>Summary</h3>
            <p>Total Pending Payments: <?php echo $result->num_rows; ?></p>
            <p>Month: <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?></p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="pending-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>Resident</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending payments for <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?>!</p>
        <?php endif; ?>
    </div>

    <a href="mess_sec_paymentstatus.php" class="back-button">Back to Payment Options</a>
    <a class="back-button" class="export-button" onclick="window.print()">Print page</a>
</body>
</html>
