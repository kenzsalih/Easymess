<?php
session_start();

// Check if user is logged in and is a Mess Secretary
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$categories = ['breakfast', 'lunch', 'snacks', 'dinner'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_menu'])) {
    foreach ($days as $day) {
        foreach ($categories as $category) {
            $meal_id = $_POST[$day . '_' . $category];
            
            // Check if menu entry exists
            $check_sql = "SELECT * FROM mess_menu WHERE day = ? AND category = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $day, $category);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing entry
                $sql = "UPDATE mess_menu SET meal_option_id = ? WHERE day = ? AND category = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $meal_id, $day, $category);
            } else {
                // Insert new entry
                $sql = "INSERT INTO mess_menu (day, category, meal_option_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $day, $category, $meal_id);
            }
            
            if (!$stmt->execute()) {
                $error_message = "Error updating menu for $day $category";
            }
        }
    }
    
    if (!isset($error_message)) {
        $success_message = "Menu updated successfully!";
    }
}

// Get popular meals from poll_responses
$popular_meals = [];
foreach ($categories as $category) {
    $sql = "SELECT mo.id, mo.option_text, COUNT(pr.id) as vote_count 
            FROM mess_poll_options mo 
            LEFT JOIN poll_responses pr ON mo.id = pr.option_id 
            WHERE mo.category = ? 
            GROUP BY mo.id 
            ORDER BY vote_count DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $popular_meals[$category] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get current menu
$current_menu = [];
$menu_sql = "SELECT m.*, mo.option_text 
             FROM mess_menu m 
             JOIN mess_poll_options mo ON m.meal_option_id = mo.id";
$menu_result = $conn->query($menu_sql);

while ($row = $menu_result->fetch_assoc()) {
    $current_menu[$row['day']][$row['category']] = [
        'id' => $row['meal_option_id'],
        'text' => $row['option_text']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Weekly Menu</title>
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
        }
        .menu-table th {
            background-color: #4e0e3a;
            color: white;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .vote-count {
            color: #666;
            font-size: 0.9em;
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
        .submit-btn {
            background-color: #4e0e3a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Create Weekly Menu</h1>
    </nav>
    <div class="container">
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
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
                                <select name="<?php echo $day . '_' . $category; ?>" required>
                                    <option value="">Select meal</option>
                                    <?php foreach ($popular_meals[$category] as $meal): ?>
                                        <option value="<?php echo $meal['id']; ?>"
                                            <?php echo (isset($current_menu[$day][$category]) && 
                                                      $current_menu[$day][$category]['id'] == $meal['id']) 
                                                      ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($meal['option_text']); ?>
                                            (<?php echo $meal['vote_count']; ?> votes)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <button type="submit" name="create_menu" class="submit-btn">Save Menu</button>
        </form>

        <a href="mess sec.php" class="back-button" onclick="window.location.">Back to Dashboard</a>
    </div>
</body>
</html>

