<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $role = $_POST['role'];

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = '$role'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        
        if ($role == 'Resident') {
            header("Location: resident.php");
        } else if ($role == 'Matron') {
            header("Location: matron.php");
        } else if ($role == 'Warden') {
            $_SESSION['role'] = 'Warden';
            header("Location: warden.php");
            exit();
        } else if ($role == 'Mess_sec') {
            header("Location: mess sec.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid credentials - Please check your username, password and role'); window.location.href='index.php';</script>";
    }
}
?>
