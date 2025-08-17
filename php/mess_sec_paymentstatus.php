<?php
session_start();

// Check if user is logged in and is a Mess Secretary
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Fetch all payment records from mess_payments table
$sql = "SELECT * FROM mess_payments ORDER BY submission_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status Options</title>
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
        .options-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-top: 50px;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .option-button {
            width: 50%;
            padding: 20px;
            font-size: 1.2em;
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .option-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0e3a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            position: absolute;
            bottom: 20px;
            left: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
    <h1>Payment Status Options</h1>
    </nav>
    <div class="options-container">
        <button class="option-button" onclick="window.location.href='mess_sec_paidlist.php'">
            Paid List 
        </button>
        <button class="option-button" onclick="window.location.href='mess_sec_pending_list.php'">
            Pending List
        </button>
    </div>

    <a href="mess sec.php" class="back-button">Back to Dashboard</a>
</body>
</html>
