<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$today = new DateTime();
$min_request_date = $today->modify('+2 days')->format('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_date = new DateTime($_POST['from_date']);
    $to_date = new DateTime($_POST['to_date']);
    
    // Extract month and year from the from_date
    $month = $from_date->format('n'); // n gives month without leading zeros
    $year = $from_date->format('Y');
    
    if ($from_date < new DateTime($min_request_date)) {
        $error_message = "Mess cut requests must be made at least two days in advance.";
    } else {
        $check_sql = "SELECT * FROM monthly_attendance WHERE username = ? AND mess_cut_from = ? AND mess_cut_to = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sss", $_SESSION['username'], $_POST['from_date'], $_POST['to_date']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "A similar mess cut request already exists.";
        } else {
            // Modified SQL to include month and year
            $sql = "INSERT INTO monthly_attendance 
                    (username, month, year, mess_cut_from, mess_cut_to, mess_cut_status) 
                    VALUES (?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiss", 
                $_SESSION['username'],
                $month,
                $year, 
                $_POST['from_date'], 
                $_POST['to_date']
            );
            
            // Debugging
            error_log("Inserting mess cut request with month: $month, year: $year, from: {$_POST['from_date']}, to: {$_POST['to_date']}");
            
            if ($stmt->execute()) {
                $success_message = "Mess cut request submitted successfully";

                // Email automation
                $recipient_email = "kenz4ppsalih@gmail.com"; // Change to actual recipient email
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'easymess2025@gmail.com'; // Your Gmail
                    $mail->Password = 'irio tsmc rkme nqoy'; // Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('kenz.idk22cs045@gecidukki.ac.in', 'Hostel Mess System');
                    $mail->addAddress($recipient_email);
                    $mail->Subject = "New Mess Cut Request - " . $_SESSION['username'];

                    // Ensure that HTML content is set
                    $mail->isHTML(true); // Set email format to HTML
                    $mail->CharSet = 'UTF-8'; // Ensure the character encoding is set correctly

                    // Create the HTML body content with the link
                    $mail->Body = "
                        <html>
                        <head>
                            <title>New Mess Cut Request</title>
                        </head>
                        <body>
                            <p>Hello,</p>
                            <p>A new mess cut request has been made:</p>
                            <p><strong>Username:</strong> " . $_SESSION['username'] . "</p>
                            <p><strong>Month:</strong> $month</p>
                            <p><strong>Year:</strong> $year</p>
                            <p><strong>Mess Cut From:</strong> {$_POST['from_date']}</p>
                            <p><strong>Mess Cut To:</strong> {$_POST['to_date']}</p>
                            <p><strong>Mess Cut Status:</strong> pending</p>
                            <p><a href='http://localhost/hostel%20mess%20management%20system/index.php' target='_blank'>Click here to review and approve the request</a></p>
                            <p>Regards,<br>Easy Mess Hostel Management</p>
                        </body>
                        </html>
                    ";

                    // Send the email
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email sending failed: " . $mail->ErrorInfo);
                    $error_message = "Failed to send the email notification.";
                }


            } else {
                $error_message = "Error submitting request: " . $stmt->error;
                error_log("Database error: " . $stmt->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Mess Cut</title>
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
            max-width: 600px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #4e0e3a;
            font-weight: bold;
        }

        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #4e0e3a;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .submit-btn {
            background: linear-gradient(45deg, #4e0e3a, #48344c);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
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

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Resident Dashboard - Request Mess Cut</h1>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" min="<?php echo $min_request_date; ?>" required>
            </div>

            <div class="form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" min="<?php echo $min_request_date; ?>" required>
            </div>

            <button type="submit" class="submit-btn">Submit Request</button>
        </form>

        <a href="resident.php" class="back-button">Back to Dashboard</a>
    </div>

    <script>
        document.getElementById('from_date').addEventListener('change', function() {
            document.getElementById('to_date').min = this.value;
        });
    </script>
</body>
</html>