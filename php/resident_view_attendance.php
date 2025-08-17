<?php
session_start();

// Check if user is logged in and is a Resident
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get month and year from URL or use current
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get total days in selected month
$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year); 

// Get attendance details for the resident - MODIFIED QUERY to get all approved mess cuts
$sql = "SELECT mess_cut_from, mess_cut_to FROM monthly_attendance 
        WHERE username = ? AND month = ? AND year = ? AND mess_cut_status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $_SESSION['username'], $month, $year);
$stmt->execute();
$result = $stmt->get_result();

// Debugging: Log query and parameters
error_log("Query: $sql with params: {$_SESSION['username']}, $month, $year");
error_log("Number of rows returned: " . $result->num_rows);

// Calculate attendance and get mess cut dates
$days_absent = 0;
$days_present = $total_days;
$percentage = 100;
$mess_cut_dates = array();

// Process all approved mess cuts
while ($attendance = $result->fetch_assoc()) {
    if ($attendance['mess_cut_from'] && $attendance['mess_cut_to']) {
        $from_date = new DateTime($attendance['mess_cut_from']);
        $to_date = new DateTime($attendance['mess_cut_to']);
        
        // Only count days that fall within the current month
        $month_start = new DateTime("$year-$month-01");
        $month_end = new DateTime("$year-$month-$total_days");
        
        // Adjust dates if they fall outside current month
        if ($from_date < $month_start) $from_date = $month_start;
        if ($to_date > $month_end) $to_date = $month_end;
        
        $interval = $from_date->diff($to_date);
        $current_days_absent = $interval->days + 1;
        
        // Debugging
        error_log("Mess cut from {$attendance['mess_cut_from']} to {$attendance['mess_cut_to']}");
        error_log("Adjusted from {$from_date->format('Y-m-d')} to {$to_date->format('Y-m-d')}");
        error_log("Days absent for this period: $current_days_absent");
        
        $days_absent += $current_days_absent;

        // Store all mess cut dates in array
        $period = new DatePeriod(
            $from_date,
            new DateInterval('P1D'),
            $to_date->modify('+1 day')
        );

        foreach ($period as $date) {
            $mess_cut_dates[] = $date->format('Y-m-d');
        }
    }
}

// Calculate final attendance numbers
$days_present = $total_days - $days_absent;
if ($days_present < 0) $days_present = 0; // Safeguard against negative values
$percentage = $total_days > 0 ? round(($days_present / $total_days) * 100, 2) : 0;

// Debugging: Log final calculations
error_log("Final days absent: $days_absent, Days present: $days_present");
error_log("Mess cut dates: " . implode(", ", $mess_cut_dates));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
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
            max-width: 800px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .month-selector {
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
        }

        select {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #4e0e3a;
            border-radius: 4px;
        }

        .calendar {
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            padding: 10px 0;
        }

        .calendar-header span {
            text-align: center;
            font-weight: bold;
        }

        .calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #eee;
        }

        .calendar-day {
            background: white;
            padding: 10px;
            text-align: center;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-day.absent {
            background: rgba(255, 0, 0, 0.1);
            color: red;
            border: 1px solid rgba(255, 0, 0, 0.3);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .stat-box {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        <h1>Resident Dashboard - My Attendance</h1>
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

        <div class="stats">
            <div class="stat-box">
                <h3><?php echo $days_present; ?></h3>
                <p>Days Present</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $days_absent; ?></h3>
                <p>Days Absent</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $percentage; ?>%</h3>
                <p>Attendance</p>
            </div>
        </div>

        <div class="calendar">
            <div class="calendar-header">
                <span>Sun</span>
                <span>Mon</span>
                <span>Tue</span>
                <span>Wed</span>
                <span>Thu</span>
                <span>Fri</span>
                <span>Sat</span>
            </div>
            <div class="calendar-body">
                <?php
                $firstDay = mktime(0, 0, 0, $month, 1, $year);
                $startingDay = date('w', $firstDay);
                
                // Add empty cells for days before the first of the month
                for ($i = 0; $i < $startingDay; $i++) {
                    echo "<div class='calendar-day'></div>";
                }
                
                // Add the days of the month
                for ($day = 1; $day <= $total_days; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $class = in_array($date, $mess_cut_dates) ? 'calendar-day absent' : 'calendar-day';
                    echo "<div class='$class'>$day</div>";
                }
                
                // Add empty cells for remaining days to complete the grid
                $remainingDays = (7 - (($startingDay + $total_days) % 7)) % 7;
                for ($i = 0; $i < $remainingDays; $i++) {
                    echo "<div class='calendar-day'></div>";
                }
                ?>
            </div>
        </div>

        <a href="resident.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
