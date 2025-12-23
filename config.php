<?php
session_start();

// Database configuration
$host = '127.0.0.1';
$username = 'root';
$password = 'root';
$database = 'arian_editory';

try {
    $pdo_check = new PDO("mysql:host=$host", $username, $password);
    $pdo_check->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    if ($result->rowCount() == 0) {
        // Database doesn't exist, redirect to install
        if (basename($_SERVER['PHP_SELF']) != 'install.php') {
            header("Location: install.php");
            exit;
        }
    }
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if (basename($_SERVER['PHP_SELF']) != 'install.php') {
        header("Location: install.php");
        exit;
    }
}

// Get app settings
function getSettings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'app_name' => 'Arian Editory',
            'app_logo' => 'https://i.ibb.co/MkDmpVCT/IMG-20250603-141338-891.webp',
            'upi_id' => 'pay@arianeditory',
            'support_email' => 'arianmonadl81@gmail.com',
            'support_phone' => '+918670214689'
        ];
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect if admin not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin/login.php");
        exit;
    }
}

$settings = getSettings();
?>
