<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Warden') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');
$month = date('m', strtotime($selected_month));
$year = date('Y', strtotime($selected_month));

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['authorize_bills'])) {
    $authorize_sql = "UPDATE mess_bills SET authorized = 1 WHERE month = ? AND year = ?";
    $authorize_stmt = $conn->prepare($authorize_sql);
    $authorize_stmt->bind_param("ii", $month, $year);
    $authorize_stmt->execute();
    $success_message = "Bills authorized successfully!";
}

$bills_sql = "SELECT mb.*, u.name FROM mess_bills mb JOIN users u ON mb.user_id = u.id WHERE mb.month = ? AND mb.year = ?";
$bills_stmt = $conn->prepare($bills_sql);
$bills_stmt->bind_param("ii", $month, $year);
$bills_stmt->execute();
$bills_result = $bills_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden View Bills</title>
    <style>
        /* Add styles similar to mess_bill_calculator.php */
    </style>
</head>
<body>
    <h1>Warden View Bills</h1>
    <?php if (isset($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="month" name="month" value="<?= $selected_month ?>" required>
        <button type="submit" name="view_bills">View Bills</button>
        <button type="submit" name="authorize_bills">Authorize Bills</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Days Present</th>
                <th>Fixed Cost</th>
                <th>Variable Cost</th>
                <th>Total Cost</th>
                <th>Authorized</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($bill = $bills_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($bill['name']) ?></td>
                    <td><?= $bill['days_present'] ?></td>
                    <td>₹<?= number_format($bill['fixed_cost'], 2) ?></td>
                    <td>₹<?= number_format($bill['variable_cost'], 2) ?></td>
                    <td>₹<?= number_format($bill['total_cost'], 2) ?></td>
                    <td><?= $bill['authorized'] ? 'Yes' : 'No' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html> 