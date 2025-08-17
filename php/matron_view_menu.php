<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostelmess";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch menu data with meal names
$sql = "SELECT m.day, m.category, m.meal_option_id, p.option_text 
        FROM mess_menu m
        JOIN mess_poll_options p ON m.meal_option_id = p.id
        ORDER BY FIELD(m.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        FIELD(m.category, 'breakfast', 'lunch','snacks', 'dinner')";

$result = $conn->query($sql);

$menu = [];
while ($row = $result->fetch_assoc()) {
    $menu[$row['day']][$row['category']] = $row['option_text'];
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$categories = ['breakfast', 'lunch','snacks', 'dinner'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Menu</title>
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
            margin: 0 auto;
        }
        .menu-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .menu-table th, .menu-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        .menu-table th {
            background-color: #4e0e3a;
            color: white;
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
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Monthly Menu</h1>
    </nav>
    <div class="container">
        
        <table class="menu-table">
            <tr>
                <th>Day</th>
                <?php foreach ($categories as $category): ?>
                    <th><?php echo ucfirst($category); ?></th>
                <?php endforeach; ?>
            </tr>
            
            <?php foreach ($days as $day): ?>
                <tr>
                    <td><strong><?php echo $day; ?></strong></td>
                    <?php foreach ($categories as $category): ?>
                        <td>
                            <?php echo isset($menu[$day][$category]) ? htmlspecialchars($menu[$day][$category]) : '-'; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <button class="back-button" onclick="window.location.href='matron.php'">Back to Dashboard</button>
    </div>
</body>
</html>