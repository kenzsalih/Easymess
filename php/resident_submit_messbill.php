<?php
session_start();

// Check if user is logged in and is a Resident
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount']; 
    $account_name = $_POST['account_name'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    
    // Handle receipt image upload
    if(isset($_FILES['receipt_image'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["receipt_image"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . $username . "_" . time() . "." . $imageFileType;
        
        // Check if image file is a actual image or fake image
        if(getimagesize($_FILES["receipt_image"]["tmp_name"]) !== false) {
            if (move_uploaded_file($_FILES["receipt_image"]["tmp_name"], $target_file)) {
                // Insert into database
                $sql = "INSERT INTO mess_payments (username, transaction_id, amount, account_name, receipt_image, month, year) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdssis", $username, $transaction_id, $amount, $account_name, $target_file, $month, $year);
                
                if($stmt->execute()) {
                    echo "<script>alert('Payment details submitted successfully!'); window.location.href='resident.php';</script>";
                } else {
                    echo "<script>alert('Error submitting payment details.');</script>";
                }
            } else {
                echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            }
        } else {
            echo "<script>alert('File is not an image.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Mess Bill</title>
    <style>
        /* Add these rules to remove the spinner buttons */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

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
            max-width: 600px;
            margin: 20px auto 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius:10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        h2 {
            color: #4e0e3a;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            opacity: 0.9;
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0e3a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 40px;
            position:absolute;
        }
        .back-button:hover {
            background-color: #3a0a2b;
        }

        .date-inputs .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Submit Mess Bill Payment Details<h1>
    </nav>
    <div class="container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="transaction_id">Transaction ID:</label>
                    <input type="text" id="transaction_id" name="transaction_id" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Transaction Amount:</label>
                    <input type="number" id="amount" name="amount" required>
                </div>
                
                <div class="form-group">
                    <label for="account_name">Sender's Bank Account Name:</label>
                    <input type="text" id="account_name" name="account_name" required>
                </div>

                <div class="date-inputs">
                    <div class="form-group">
                        <label for="month">Month:</label>
                        <select id="month" name="month" required>
                            <option value="">Select Month</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = date('Y');
                            for($i = $currentYear - 2; $i <= $currentYear; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="receipt_image">Upload Receipt Screenshot (JPG only):</label>
                    <input type="file" id="receipt_image" name="receipt_image" accept=".jpg,.jpeg" required>
                </div>
                
                <button type="submit" class="btn">Submit Payment Details</button>
            </form>
        <a href="resident.php" class="back-button">Back to Dashboard</a>
    </div>
   
    
</body>
</html>
