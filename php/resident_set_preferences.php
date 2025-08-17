<?php
session_start();

// Check if user is logged in and is a Resident
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// First check the poll status for current date
$current_date = date('Y-m-d');
$sql = "SELECT status FROM mess_poll_status WHERE poll_date = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
$poll_status = $result->fetch_assoc();

// If no poll exists for today or if poll is not open, show error
if (!$poll_status || $poll_status['status'] !== 'open') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Poll Not Available</title>
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
            .error-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                text-align: center;
            }
            .error-message {
                color: #721c24;
                background-color: #f8d7da;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
            .back-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #4e0e3a;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <nav class="navbar">
            <h1>Menu Poll</h1>
        </nav>
        <div class="error-container">
            <div class="error-message">
                <?php 
                if (!$poll_status) {
                    echo "No poll has been created for today.";
                } elseif ($poll_status['status'] === 'pending') {
                    echo "The poll for today has not been opened yet.";
                } else {
                    echo "The poll for today has been closed.";
                }
                ?>
            </div>
            <a href="resident.php" class="back-button">Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_preferences'])) {
    // Validate if exactly 7 options are selected for each category
    $categories = ['breakfast', 'lunch', 'snacks', 'dinner'];
    foreach ($categories as $category) {
        if (!isset($_POST[$category]) || count($_POST[$category]) != 7) {
            $error = "Please select exactly 7 options for " . ucfirst($category);
            break;
        }
    }
    
    if (empty($error)) {
        // Process the preferences
        $username = $_SESSION['username'];
        
        // First delete any existing preferences for this user
        $delete_sql = "DELETE FROM poll_responses WHERE username = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $username);
        $delete_stmt->execute();
        
        // Insert new preferences
        $insert_sql = "INSERT INTO poll_responses (username, option_id, category) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        foreach ($categories as $category) {
            foreach ($_POST[$category] as $option_id) {
                $insert_stmt->bind_param("sis", $username, $option_id, $category);
                $insert_stmt->execute();
            }
        }
        
        $message = "Your preferences have been saved successfully!";
    }
}

// Fetch meal options for each category from mess_poll_options table
$categories = ['breakfast', 'lunch', 'snacks', 'dinner'];
$options = [];
foreach ($categories as $category) {
    $sql = "SELECT id, option_text FROM mess_poll_options WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $options[$category] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// When creating a new poll
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_poll'])) {
    $poll_date = $_POST['poll_date'];
    $status = $_POST['status']; // Can be 'pending', 'open', or 'closed'
    
    // Insert/Update in mess_poll_status table
    $sql = "INSERT INTO mess_poll_status (poll_date, status) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $poll_date, $status, $status);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Preferences</title>
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
        .category-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4e0e3a;
            margin-bottom: 15px;
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .option-item {
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option-item input[type="checkbox"] {
            display: none;
        }
        .option-item label {
            display: block;
            width: 100%;
            cursor: pointer;
        }
        .option-item.selected {
            background: #4e0e3a;
            color: white;
        }
        .error {
            color: red;
            padding: 10px;
            margin: 10px 0;
            background: #ffe6e6;
            border-radius: 4px;
        }
        .success {
            color: green;
            padding: 10px;
            margin: 10px 0;
            background: #e6ffe6;
            border-radius: 4px;
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
        .submit-btn:hover {
            background-color: #3a0a2b;
        }
        .selection-counter {
            margin-top: 10px;
            font-weight: bold;
            color: #4e0e3a;
        }
    </style>
    <script>
        function updateCounter(category, element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('selected');
            
            const checkboxes = document.querySelectorAll(`input[name="${category}[]"]:checked`);
            const counter = document.getElementById(`${category}-counter`);
            const maxAllowed = 7;
            
            counter.textContent = `Selected: ${checkboxes.length}/7`;
            
            if (checkboxes.length > maxAllowed) {
                alert(`You can only select 7 options for ${category}!`);
                checkbox.checked = false;
                element.classList.remove('selected');
                counter.textContent = `Selected: ${maxAllowed}/7`;
            }
            
            // Disable remaining options if max is reached
            const allOptions = document.querySelectorAll(`.option-item[data-category="${category}"]`);
            allOptions.forEach(option => {
                const isChecked = option.querySelector('input[type="checkbox"]').checked;
                if (!isChecked) {
                    option.style.pointerEvents = checkboxes.length >= maxAllowed ? 'none' : 'auto';
                    option.style.opacity = checkboxes.length >= maxAllowed ? '0.5' : '1';
                }
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
        <h1>Select Meal Preferences</h1>
        </nav>  
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php foreach ($categories as $category): ?>
                <div class="category-section">
                    <h2><?php echo ucfirst($category); ?></h2>
                    <p>Please select exactly 7 options</p>
                    <div class="selection-counter" id="<?php echo $category; ?>-counter">Selected: 0/7</div>
                    <div class="options-grid">
                        <?php foreach ($options[$category] as $option): ?>
                            <div class="option-item" data-category="<?php echo $category; ?>" onclick="updateCounter('<?php echo $category; ?>', this)">
                                <input type="checkbox" 
                                       name="<?php echo $category; ?>[]" 
                                       value="<?php echo $option['id']; ?>">
                                <label><?php echo htmlspecialchars($option['option_text']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" name="submit_preferences" class="submit-btn">Submit Preferences</button>
        </form>
    </div>
    
    <a href="resident.php" class="back-button" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #4e0e3a;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 20px;
        margin-left: 20px;">Back to Dashboard</a>
</body>
</html>
