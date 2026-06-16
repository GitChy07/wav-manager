<?php
// src/includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$error_message = '';

// Pfad für deine eigene Log-Datei direkt im Hauptordner 'wav_manager'
$local_log = __DIR__ . '/../../debug.log';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Eigener Log: Erfolg registrieren
            $log_msg = "[" . date('Y-m-d H:i:s') . "] SUCCESS: Producer '" . $username . "' ist eingeloggt.\n";
            file_put_contents($local_log, $log_msg, FILE_APPEND);
            
            header("Location: index.php");
            exit;
        } else {
            // Eigener Log: Fehlgeschlagenen Versuch registrieren
            $reason = !$user ? "User nicht gefunden" : "Passwort falsch";
            $log_msg = "[" . date('Y-m-d H:i:s') . "] FAILED: Login für '" . $username . "' fehlgeschlagen. Grund: $reason\n";
            file_put_contents($local_log, $log_msg, FILE_APPEND);
            
            $error_message = "ACCESS DENIED: Invalid Name or Password!";
        }
    } else {
        $error_message = "Please fill in all fields!";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>