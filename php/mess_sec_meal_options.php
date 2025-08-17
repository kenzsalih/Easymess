<?php
session_start();

// Check if user is logged in and is a Mess Secretary
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Handle form submission for adding multiple meal options
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_options'])) {
    $category = $_POST['category'];
    $options = explode("\n", trim($_POST['options']));
    
    $success_count = 0;
    $error_count = 0;
    
    // Prepare the insert statement
    $sql = "INSERT INTO mess_poll_options (category, option_text) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($options as $option) {
        $option = trim($option);
        if (!empty($option)) {
            $stmt->bind_param("ss", $category, $option);
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
    }
    
    if ($success_count > 0) {
        $success_message = "$success_count meal options added successfully!";
    }
    if ($error_count > 0) {
        $error_message = "$error_count meal options failed to add.";
    }
}

// Handle deletion of meal options
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_options'])) {
    if(isset($_POST['options_to_delete']) && !empty($_POST['options_to_delete'])) {
        $options_to_delete = $_POST['options_to_delete'];
        
        $sql = "DELETE FROM mess_poll_options WHERE id IN (" . str_repeat('?,', count($options_to_delete) - 1) . '?)';
        $stmt = $conn->prepare($sql);
        $types = str_repeat('i', count($options_to_delete));
        $stmt->bind_param($types, ...$options_to_delete);
        
        if ($stmt->execute()) {
            $success_message = "Selected meal options deleted successfully!";
        } else {
            $error_message = "Error deleting meal options.";
        }
    } else {
        $error_message = "Please select at least one option to delete.";
    }
}

// Handle poll status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_poll'])) {
    $poll_date = $_POST['poll_date'];
    $status = $_POST['status'];
    $current_time = date('Y-m-d H:i:s');
    
    // Check if poll exists for this date
    $check_sql = "SELECT * FROM mess_poll_status WHERE poll_date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $poll_date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing poll
        $sql = "UPDATE mess_poll_status SET status = ?, 
                opened_at = CASE WHEN status = 'open' THEN ? ELSE opened_at END,
                closed_at = CASE WHEN status = 'closed' THEN ? ELSE closed_at END
                WHERE poll_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $status, $current_time, $current_time, $poll_date);
    } else {
        // Create new poll
        $sql = "INSERT INTO mess_poll_status (poll_date, status, opened_at) 
                VALUES (?, ?, CASE WHEN ? = 'open' THEN ? ELSE NULL END)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $poll_date, $status, $status, $current_time);
    }
    
    if ($stmt->execute()) {
        $success_message = "Poll status updated successfully!";
    } else {
        $error_message = "Error updating poll status.";
    }
}

// Fetch existing meal options
$categories = ['breakfast', 'lunch', 'snacks', 'dinner'];
$options_by_category = [];
foreach ($categories as $category) {
    $sql = "SELECT * FROM mess_poll_options WHERE category = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $options_by_category[$category] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch active polls
$sql = "SELECT * FROM mess_poll_status WHERE poll_date >= CURDATE() ORDER BY poll_date ASC";
$active_polls = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meal Options</title>
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
            padding: 20px;
        }
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input[type="text"], input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        button {
            background-color: #4e0e3a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #3a0a2b;
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
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .category-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
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
        .meal-item {
            margin-bottom: 5px;
            padding: 5px;
        }
        .meal-item input[type="checkbox"] {
            margin-right: 10px;
            display: none;
        }
        .remove-button {
            background-color: #721c24;
            margin-top: 10px;
        }
        .remove-button:hover {
            background-color: #c82333;
        }
        .delete-mode .meal-item input[type="checkbox"] {
            display: inline-block;
        }
        textarea {
            width: 100%;
            min-height: 150px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        .instructions {
            color: #666;
            margin-bottom: 10px;
            font-style: italic;
        }
    </style>
    <script>
        function toggleDeleteMode() {
            const form = document.getElementById('meal-options-form');
            form.classList.toggle('delete-mode');
            const removeBtn = document.getElementById('remove-btn');
            const deleteBtn = document.getElementById('delete-btn');
            removeBtn.style.display = removeBtn.style.display === 'none' ? 'block' : 'none';
            deleteBtn.style.display = deleteBtn.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <nav class="navbar">
        <h1>Create Meal Poll</h1>
    </nav>
    <div class="container">
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="section">
            <h2>Add Multiple Meal Options</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" required>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="snacks">Snacks</option>
                        <option value="dinner">Dinner</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="options">Enter Meal Options (one per line):</label>
                    <p class="instructions">Enter each meal option on a new line. Empty lines will be ignored.</p>
                    <textarea name="options" required placeholder="Dosa
Idli
Pongal
Poori
..."></textarea>
                </div>
                <button type="submit" name="add_options">Add Options</button>
            </form>
        </div>

        <div class="section">
            <h2>Manage Poll Status</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="poll_date">Poll Date:</label>
                    <input type="date" name="poll_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" required>
                        <option value="pending">Pending</option>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <button type="submit" name="update_poll">Update Poll Status</button>
            </form>
        </div>

        <div class="section">
            <h2>Existing Meal Options</h2>
            <form method="POST" id="meal-options-form">
                <div class="options-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-section">
                            <h3><?php echo ucfirst($category); ?></h3>
                            <ul style="list-style-type: none; padding: 0;">
                                <?php foreach ($options_by_category[$category] as $option): ?>
                                    <li class="meal-item">
                                        <input type="checkbox" name="options_to_delete[]" value="<?php echo $option['id']; ?>">
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="remove-btn" onclick="toggleDeleteMode()" style="display: block;">Remove Options</button>
                <button type="submit" id="delete-btn" name="delete_options" class="remove-button" style="display: none;">Delete Selected</button>
            </form>
        </div>

        <div class="section">
            <h2>Active Polls</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Opened At</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Closed At</th>
                </tr>
                <?php foreach ($active_polls as $poll): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php echo $poll['poll_date']; ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php echo ucfirst($poll['status']); ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php echo $poll['opened_at'] ?? '-'; ?>
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            <?php echo $poll['closed_at'] ?? '-'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <a href="mess sec.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
