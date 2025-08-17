<?php
session_start();

// Check if user is logged in and is a Mess Secretary
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Get all categories
$categories = ['breakfast', 'lunch', 'snacks', 'dinner'];

// Fetch poll results for each category
$results = [];
foreach ($categories as $category) {
    $sql = "SELECT m.id, m.option_text, COUNT(p.id) as votes
            FROM mess_poll_options m
            LEFT JOIN poll_responses p ON m.id = p.option_id
            WHERE m.category = ?
            GROUP BY m.id, m.option_text
            ORDER BY votes DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $results[$category] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results</title>
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
        .category-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4e0e3a;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .results-table th, .results-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .results-table th {
            background-color: #4e0e3a;
            color: white;
        }
        .results-table tr:hover {
            background-color: #f9f9f9;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0e3a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #3a0a2b;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Mess Meal Poll Results</h1>
    </nav>
    <div class="container">
        <?php foreach ($categories as $category): ?>
            <div class="category-section">
                <h2><?php echo ucfirst($category); ?></h2>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Meal Option</th>
                            <th>Number of Votes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results[$category] as $result): ?>
                            <?php if ($result['votes'] > 0): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['option_text']); ?></td>
                                    <td><?php echo htmlspecialchars($result['votes']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <a href="resident.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>