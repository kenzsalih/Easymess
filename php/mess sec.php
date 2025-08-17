<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Secretary Dashboard - Hostel Mess Management System</title>
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

        .navbar-links {
            display: flex;
            gap: 20px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .navbar-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card h2 {
            color: #4e0e3a;
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }

        .dashboard-card p {
            margin: 0.75rem 0;
            color: #666;
        }

        .action-button {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0.5rem auto;
            width: 80%;
            display: block;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-button:hover {
            opacity: 0.9;
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .export-button {
            background-color: #4e0e3a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 0;
        }

        .inventory-card {
            grid-column: 2;
            grid-row: 2;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <h1>Mess Secretary Dashboard</h1>
        <div class="navbar-links">
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-grid">
        <div class="dashboard-card">
    <h2>Attendance Monitoring</h2>
    <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
        <button class="action-button" onclick="window.location.href='mess_sec_monthly_attendance.php'">View Attendance</button>
    </div>
</div> 

            <div class="dashboard-card">
                <h2>Mess Bill Monitoring</h2>
                <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
                    <button class="action-button" onclick="window.location.href='mess_bill_calculator.php'">Bill Calculation</button>
                    <button class="action-button" onclick="window.location.href='mess_sec_paymentstatus.php'">Payment Status</button></div>
            </div>

            <div class="dashboard-card">
                <h2>Mess Poll Details</h2>
                <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
                    <button class="action-button" onclick="window.location.href='mess_sec_view_polls.php'">View Polls</button>
                    <button class="action-button" onclick="window.location.href='mess_sec_meal_options.php'">Create Poll</button>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Menu Management</h2>
                <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
                <button class="action-button" onclick="window.location.href='mess_sec_menu_creation.php'">edit menu</button>
                <button class="action-button" onclick="window.location.href='mess_sec_view_menu.php'">view menu</button>
            </div>
            </div>

            <div class="dashboard-card inventory-card">
                <h2>Inventory Management</h2>
                <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
                    <button class="action-button" onclick="window.location.href='mess_sec_inventory.php'">Update Inventory</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>