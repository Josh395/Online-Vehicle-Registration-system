<?php
// config.php

// Check if session is already started before starting a new one
if (session_status() === PHP_SESSION_NONE) {
    // session_start(); // Commented out to prevent duplicate session
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'vor');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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

// Redirect if not admin
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: admin/login.php");
        exit;
    }
}

// Set timezone
date_default_timezone_set('Africa/Dar_es_Salaam');
?>