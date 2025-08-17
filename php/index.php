<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
session_destroy();  
session_start();
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    if ($_SESSION['role'] == 'Resident') {
        header("Location: resident.php");
    } else if ($_SESSION['role'] == 'matron') {
        header("Location: matron.php"); 
    } else if ($_SESSION['role'] == 'Warden') {
        header("Location: warden.php");
    } else if ($_SESSION['role'] == 'Mess_sec') {
        header("Location: mess_sec.php");
    }
    exit();
}

// Also check any form action or redirect after login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    
    // Update these redirections too
    if ($role == 'Resident') {
        header("Location: resident.php");
    } else if ($role == 'matron') {
        header("Location: matron.php");
    } else if ($role == 'Warden') {
        header("Location: warden.php");
    } else if ($role == 'Mess_sec') {
        header("Location: mess_sec.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Mess Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('https://imgs.search.brave.com/XAaBZm7wAJa1cNK6eRTjTUkGt-5zsENVfJvBr2YPGiA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly90My5m/dGNkbi5uZXQvanBn/LzA2LzI5LzA3LzIy/LzM2MF9GXzYyOTA3/MjI5NF9sN05nR0VV/dnJRT01KWHFuMTB3/cEY0SEdockRmRVRP/bS5qcGc');
            background-size: cover;
            background-position: center;
            backdrop-filter: blur(5px);
            background-repeat: no-repeat;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            color: #555;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .login-btn {
            background-color: #4a90e2;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #357abd;
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: #4a90e2;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Hostel Mess Management System</h1>
            <p>Please login to continue</p>
        </div>
        <form class="login-form" action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="Resident">Resident</option>
                    <option value="Matron">Matron</option>
                    <option value="Warden">Warden</option>
                    <option value="Mess_sec">Mess Secretary</option>
                </select>
            </div>
            <button type="submit" class="login-btn">Login</button>
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </form>
    </div>
</body>
</html>
