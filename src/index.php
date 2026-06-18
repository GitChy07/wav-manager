<?php
// src/index.php

// Lädt die Auth-Logik (liegt im selben Ordner unter 'includes')
require_once __DIR__ . '/includes/auth.php';

$error_message = '';

// Logout verarbeiten (Kompetenz C9)
if (isset($_GET['logout'])) {
    logoutUser();
}

// Wenn der Login-Button geklickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $login_result = loginUser($pdo, $username, $password);
    if ($login_result !== true) {
        $error_message = $login_result;
    }
}

// ROUTING: Welches Template wird angezeigt?
if (isLoggedIn()) {
    
    // ----------------------------------------------------
    // FALL A: PRODUCER IST EINGELOGGT -> EXPLORER VIEW
    // ----------------------------------------------------
    $header = file_get_contents(__DIR__ . '/templates/header.html');
    $user_status_html = '<span>Producer: <strong>' . htmlspecialchars($_SESSION['username']) . '</strong></span>' .
                        '<a href="profile.php" style="color: #4facfe; text-decoration: none; margin: 0 10px;">[SETTINGS]</a>' .
                        '<a href="?logout=1" style="text-decoration: none;">[LOGOUT]</a>';
    echo str_replace('{{USER_STATUS}}', $user_status_html, $header);
    
    echo file_get_contents(__DIR__ . '/templates/explorer-view.html');
    echo file_get_contents(__DIR__ . '/templates/footer.html');

} else {
    
    // ----------------------------------------------------
    // FALL B: PRODUCER IST NICHT EINGELOGGT -> LOGIN FORMULAR
    // ----------------------------------------------------
    $header = file_get_contents(__DIR__ . '/templates/header.html');
    echo str_replace('{{USER_STATUS}}', '', $header);
    
    // Wir laden dein FL-Studio-Login-Formular
    $login_html = file_get_contents(__DIR__ . '/templates/login-form.html');
    
    // Falls ein Login-Fehler vorliegt, bauen wir die Error-Box passend zu deinem Design ein
    $error_html = '';
    if (!empty($error_message)) {
        $error_html = '<div class="fl-window-error" style="color: #ff5555; background: #2a2a2a; border: 1px solid #ff5555; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace; font-size: 12px; border-radius: 3px;">' . htmlspecialchars($error_message) . '</div>';
    }
    
    // Der Fehler wird elegant direkt vor deinem <form>-Tag eingeschleust
    $login_html = str_replace('<form', $error_html . '<form', $login_html);
    
    echo $login_html;
    echo file_get_contents(__DIR__ . '/templates/footer.html');
}
?>