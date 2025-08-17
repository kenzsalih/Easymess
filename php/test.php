<?php
session_start();
include 'db_connect.php';

// Test connection
echo "Testing database connection...<br>";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!<br><br>";

// Check mess_bills table structure
echo "Checking mess_bills table structure...<br>";
$result = $conn->query("DESCRIBE mess_bills");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".$row['Field']."</td>";
        echo "<td>".$row['Type']."</td>";
        echo "<td>".$row['Null']."</td>";
        echo "<td>".$row['Key']."</td>";
        echo "<td>".$row['Default']."</td>";
        echo "<td>".$row['Extra']."</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing table: " . $conn->error;
}

// Check if any data exists
echo "<br>Checking for existing data...<br>";
$result = $conn->query("SELECT COUNT(*) as count FROM mess_bills");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Rows in mess_bills: " . $row['count'];
} else {
    echo "Error counting rows: " . $conn->error;
}

$conn->close();
?>