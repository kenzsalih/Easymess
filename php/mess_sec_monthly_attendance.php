<?php
session_start();

// Check if user is logged in and is a Matron
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get month and year from URL or use current
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get total days in selected month
$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Fetch all residents
$sql = "SELECT username FROM users WHERE role = 'Resident' ORDER BY username";
$residents_result = $conn->query($sql);
$residents = [];
while ($row = $residents_result->fetch_assoc()) {
    $residents[$row['username']] = [
        'mess_cut_days' => 0
    ];
}

// Fetch mess cut details and sum up days per user
$sql = "SELECT username, mess_cut_from, mess_cut_to FROM monthly_attendance 
        WHERE month = ? AND year = ? AND mess_cut_status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $from_date = new DateTime($row['mess_cut_from']);
    $to_date = new DateTime($row['mess_cut_to']);
    $interval = $from_date->diff($to_date);
    $days_absent = $interval->days + 1;
    
    if (isset($residents[$row['username']])) {
        $residents[$row['username']]['mess_cut_days'] += $days_absent;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Report</title>
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

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .attendance-table th {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
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
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Matron Dashboard - Attendance Report</h1>
    </div>

    <div class="container">
        <div class="month-selector">
            <form method="GET">
                <select name="month" onchange="this.form.submit()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" 
                                <?php echo $m == $month ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select name="year" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                        <option value="<?php echo $y; ?>" 
                                <?php echo $y == $year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Days Present</th>
                    <th>Total Days</th>
                    <th>Percentage</th>
                    <th>Mess Cut Days</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($residents as $username => $data): ?>
                    <?php 
                    $days_present = $total_days - $data['mess_cut_days'];
                    $percentage = round(($days_present / $total_days) * 100, 2);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($username); ?></td>
                        <td><?php echo $days_present; ?></td>
                        <td><?php echo $total_days; ?></td>
                        <td><?php echo $percentage; ?>%</td>
                        <td><?php echo $data['mess_cut_days']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="mess sec.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
