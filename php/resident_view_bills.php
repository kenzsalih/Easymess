<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Resident') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$username = $_SESSION['username'];
$user_sql = "SELECT id FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$bills_sql = "SELECT * FROM mess_bills WHERE user_id = ? AND authorized = 1";
$bills_stmt = $conn->prepare($bills_sql);
$bills_stmt->bind_param("i", $user['id']);
$bills_stmt->execute();
$bills_result = $bills_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident View Bills</title>
    <style>
        /* Add styles similar to mess_bill_calculator.php */
    </style>
</head>
<body>
    <h1>Resident View Bills</h1>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Days Present</th>
                <th>Fixed Cost</th>
                <th>Variable Cost</th>
                <th>Total Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bill = $bills_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $bill['month'] ?></td>
                    <td><?= $bill['year'] ?></td>
                    <td><?= $bill['days_present'] ?></td>
                    <td>₹<?= number_format($bill['fixed_cost'], 2) ?></td>
                    <td>₹<?= number_format($bill['variable_cost'], 2) ?></td>
                    <td>₹<?= number_format($bill['total_cost'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html> 