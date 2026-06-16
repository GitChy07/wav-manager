<?php
// src/index.php

// Lädt die Auth-Logik (liegt im selben Ordner unter 'includes')
require_once __DIR__ . '/includes/auth.php';

// ROUTING: Welches Template wird angezeigt?
if (isLoggedIn()) {
    
    // ----------------------------------------------------
    // FALL A: PRODUCER IST EINGELOGGT -> EXPLORER VIEW
    // ----------------------------------------------------
    echo file_get_contents(__DIR__ . '/templates/header.html');
    echo file_get_contents(__DIR__ . '/templates/explorer-view.html');
    echo file_get_contents(__DIR__ . '/templates/footer.html');

} else {
    
    // ----------------------------------------------------
    // FALL B: PRODUCER IST NICHT EINGELOGGT -> LOGIN FORMULAR
    // ----------------------------------------------------
    echo file_get_contents(__DIR__ . '/templates/header.html');
    
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