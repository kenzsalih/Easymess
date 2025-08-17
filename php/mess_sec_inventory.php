<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'Mess_sec') {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        $item_name = sanitizeInput($_POST['item_name']);
        $quantity = floatval($_POST['quantity']);
        $unit = sanitizeInput($_POST['unit']);
        $vendor = sanitizeInput($_POST['vendor']);
        $purchase_date = $_POST['purchase_date'];
        $cost = floatval($_POST['cost']);

        $check_sql = "SELECT id, quantity FROM inventory_items WHERE item_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $item_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $existing_item = $check_result->fetch_assoc();
            $total_quantity = $existing_item['quantity'] + $quantity;
            $update_sql = "UPDATE inventory_items SET quantity = ?, last_purchase_quantity = ?, last_purchase_date = ?, last_purchase_cost = ?, last_updated = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dssdi", $total_quantity, $quantity, $purchase_date, $cost, $existing_item['id']);
            
            if ($update_stmt->execute()) {
                $cost_sql = "INSERT INTO inventory_costs (item_id, purchase_date, quantity, cost) VALUES (?, ?, ?, ?)";
                $cost_stmt = $conn->prepare($cost_sql);
                $cost_stmt->bind_param("isdd", $existing_item['id'], $purchase_date, $quantity, $cost);
                $cost_stmt->execute();
                
                $success_message = "Item quantity and cost updated successfully";
            } else {
                $error_message = "Error updating item quantity";
            }
        } else {
            $insert_sql = "INSERT INTO inventory_items (item_name, quantity, last_purchase_quantity, unit, vendor, purchase_date, last_purchase_date, last_purchase_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sddssssd", $item_name, $quantity, $quantity, $unit, $vendor, $purchase_date, $purchase_date, $cost);
            
            if ($insert_stmt->execute()) {
                $item_id = $conn->insert_id;
                
                $cost_sql = "INSERT INTO inventory_costs (item_id, purchase_date, quantity, cost) VALUES (?, ?, ?, ?)";
                $cost_stmt = $conn->prepare($cost_sql);
                $cost_stmt->bind_param("isdd", $item_id, $purchase_date, $quantity, $cost);
                $cost_stmt->execute();
                
                $success_message = "New item added successfully";
            } else {
                $error_message = "Error adding new item";
            }
        }
    }

    if (isset($_POST['update_usage'])) {
        $item_id = intval($_POST['item_id']);
        $used_quantity = floatval($_POST['used_quantity']);
        $username = $_SESSION['username'];

        $conn->begin_transaction();
        
        $update_sql = "UPDATE inventory_items SET quantity = quantity - ?, last_updated = CURRENT_TIMESTAMP WHERE id = ? AND quantity >= ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ddd", $used_quantity, $item_id, $used_quantity);

        if ($update_stmt->execute()) {
            $transaction_sql = "INSERT INTO inventory_transactions (item_id, transaction_type, quantity, transaction_date, created_by) VALUES (?, 'usage', ?, NOW(), ?)";
            $transaction_stmt = $conn->prepare($transaction_sql);
            $transaction_stmt->bind_param("ids", $item_id, $used_quantity, $username);

            if ($transaction_stmt->execute()) {
                $conn->commit();
                $success_message = "Usage updated successfully";
            } else {
                $conn->rollback();
                $error_message = "Error recording transaction";
            }
        } else {
            $error_message = "Error updating usage";
        }
    }

    if (isset($_POST['add_fixed_cost'])) {
        $cost_type = sanitizeInput($_POST['cost_type']);
        $amount = floatval($_POST['amount']);
        $month = $_POST['month'];
        
        $fixed_cost_sql = "INSERT INTO fixed_costs (cost_type, amount, month) VALUES (?, ?, ?)";
        $fixed_cost_stmt = $conn->prepare($fixed_cost_sql);
        $fixed_cost_stmt->bind_param("sds", $cost_type, $amount, $month);
        
        if ($fixed_cost_stmt->execute()) {
            $success_message = "Fixed cost added successfully";
        } else {
            $error_message = "Error adding fixed cost";
        }
    }
}

$inventory_sql = "SELECT 
    id, 
    item_name, 
    quantity, 
    last_purchase_quantity,
    unit, 
    vendor,
    last_purchase_date
    FROM inventory_items 
    ORDER BY item_name";
$inventory_result = $conn->query($inventory_sql);

// Get all item names for the dropdown
$items_sql = "SELECT id, item_name FROM inventory_items ORDER BY item_name";
$items_result = $conn->query($items_sql);

// Add query to get monthly costs
$current_month = date('Y-m');
$monthly_costs_sql = "SELECT SUM(cost) as total_variable_cost FROM inventory_costs WHERE DATE_FORMAT(purchase_date, '%Y-%m') = ?";
$monthly_costs_stmt = $conn->prepare($monthly_costs_sql);
$monthly_costs_stmt->bind_param("s", $current_month);
$monthly_costs_stmt->execute();
$monthly_costs_result = $monthly_costs_stmt->get_result()->fetch_assoc();

$fixed_costs_sql = "SELECT SUM(amount) as total_fixed_cost FROM fixed_costs WHERE month = ?";
$fixed_costs_stmt = $conn->prepare($fixed_costs_sql);
$fixed_costs_stmt->bind_param("s", $current_month);
$fixed_costs_stmt->execute();
$fixed_costs_result = $fixed_costs_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Inventory Management</title>
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
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0e3a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .action-button:hover {
            opacity: 0.9;
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-section {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .inventory-table th, .inventory-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .inventory-table th {
            background-color: #4e0e3a;
            color: white;
        }

        .submit-btn {
            background-color: #4e0e3a;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }

        select {
            padding: 8px 12px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
            width: 200px;
        }
        
        select:focus {
            border-color: #4e0e3a;
            outline: none;
        }
        
        .form-section h2 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #4e0e3a;
        }

        .form-section form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Mess Inventory Management</h1>
        <div class="navbar-links">
        </div>
    </nav>

    <div class="dashboard-container">
        <?php
        if (isset($success_message)) echo "<div class='message success'>$success_message</div>";
        if (isset($error_message)) echo "<div class='message error'>$error_message</div>";
        ?>

        <div class="form-section">
            <h2>Add/Update Item</h2>
            <form method="POST">
                <input type="text" name="item_name" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" step="0.01" required>
                <input type="text" name="unit" placeholder="Unit (kg, liters, etc.)" required>
                <input type="text" name="vendor" placeholder="Vendor Name" required>
                <input type="number" name="cost" placeholder="Total Cost (₹)" step="1" required>
                <input type="date" name="purchase_date" required>
                <button type="submit" name="add_item" class="submit-btn">Add/Update Item</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Add Fixed Cost</h2>
            <form method="POST">
                <input type="text" name="cost_type" placeholder="Cost Type (e.g., Salary, Newspaper)" required>
                <input type="number" name="amount" placeholder="Amount (₹)" step="0.01" required>
                <input type="month" name="month" required>
                <button type="submit" name="add_fixed_cost" class="submit-btn">Add Fixed Cost</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Update Usage</h2>
            <form method="POST">
                <select name="item_id" required>
                    <option value="">Select Item</option>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="used_quantity" placeholder="Used Quantity" step="0.01" required>
                <button type="submit" name="update_usage" class="submit-btn">Update Usage</button>
            </form>
        </div>

        <div class="dashboard-card">
            <h2>Monthly Cost Summary (<?= date('F Y') ?>)</h2>
            <p>Total Variable Cost: ₹<?= number_format($monthly_costs_result['total_variable_cost'], 2) ?></p>
            <p>Total Fixed Cost: ₹<?= number_format($fixed_costs_result['total_fixed_cost'], 2) ?></p>
            <p>Total Cost: ₹<?= number_format($monthly_costs_result['total_variable_cost'] + $fixed_costs_result['total_fixed_cost'], 2) ?></p>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Current Quantity</th>
                    <th>Last Purchase Quantity</th>
                    <th>Unit</th>
                    <th>Vendor</th>
                    <th>Last Purchase Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $inventory_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['last_purchase_quantity'] ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td><?= htmlspecialchars($row['vendor']) ?></td>
                        <td><?= $row['last_purchase_date'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div style="margin-top: 30px; text-align: left;">
            <button class="action-button" onclick="window.location.href='mess sec.php'">Back to Dashboard</button>
        </div>
        
    </div>
</body>
</html>
