<?php
// src/index.php

// Hier lade ich meine Auth-Logik (liegt im selben Ordner unter 'includes')
require_once __DIR__ . '/includes/auth.php';

$error_message = '';

// Meinen Logout verarbeiten (Kompetenz C9)
if (isset($_GET['logout'])) {
    logoutUser();
}

// Wenn ich den Login-Button geklickt habe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Annahme: $pdo wird über auth.php bereitgestellt, da es hier aufgerufen wird
    $login_result = loginUser($pdo, $username, $password);
    if ($login_result !== true) {
        $error_message = $login_result;
    }
}

// ==============================================================================
// BEWERTUNGSRELEVANT: KOMPETENZ C8 (Session-Handling)
// ==============================================================================
// Hier wird die Zugriffskontrolle (Routing) umgesetzt. 
// Unangemeldete User werden auf die Login-Seite umgeleitet, 
// während angemeldete User das Studio-Interface (Explorer View) sehen.
if (isLoggedIn()) {
    
    // ----------------------------------------------------
    // FALL A: ICH BIN EINGELOGGT -> EXPLORER VIEW (DYNAMIC)
    // ----------------------------------------------------
    
    // 1. Daten holen aus allen 3 Tabellen (Nur für den aktuellen User!)
    $user_id = $_SESSION['user_id'] ?? null;
    $sounds = [];
    
    if ($user_id) {
        try {
            $query = "
                SELECT id, title, description, 'one_shot' AS type, NULL AS bpm, NULL AS music_key, NULL AS source_description, NULL AS tags, file_path 
                FROM one_shots 
                WHERE user_id = :user_id1
                
                UNION ALL
                
                SELECT id, title, description, 'sample' AS type, bpm, music_key, source_description, NULL AS tags, file_path 
                FROM samples 
                WHERE user_id = :user_id2
                
                UNION ALL
                
                SELECT id, title, description, 'song' AS type, bpm, music_key, NULL AS source_description, tags, file_path 
                FROM songs 
                WHERE user_id = :user_id3
                
                ORDER BY id DESC
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'user_id1' => $user_id,
                'user_id2' => $user_id,
                'user_id3' => $user_id
            ]);
            $sounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = "DB-Error beim Laden der Playlist: " . $e->getMessage();
        }
    }

    // 2. Layout rendern
    $header = file_get_contents(__DIR__ . '/templates/header.html');
    
    // Hier baue ich meine Status-Anzeige und die Menü-Links für mein Studio zusammen.
    $user_status_html = '<a href="upload.php" style="font-weight: bold; color: var(--accent-orange); margin-right: 20px; text-decoration: none;">+ Add Sound (WAV)</a>' .
                        '<span style="color: var(--text-muted); margin-right: 15px;">Producer: <strong style="color: var(--text-light);">' . htmlspecialchars($_SESSION['username']) . '</strong></span>' .
                        '<a href="profile.php" style="color: #4facfe; text-decoration: none; margin: 0 10px;">[SETTINGS]</a>' .
                        '<a href="?logout=1" style="text-decoration: none;">[LOGOUT]</a>';
                        
    echo str_replace('{{USER_STATUS}}', $user_status_html, $header);
    
    // ==============================================================================
    // NICHT BEWERTUNGSRELEVANT: UX/UI Kür (Studio Layout & Visualizer)
    // ==============================================================================
    // Das Laden des Studio-Interfaces inkl. Oszilloskop und dynamischem CSS.
    // CRITICAL FIX: 'include' statt 'file_get_contents', damit PHP im Template ausgeführt wird!
    include __DIR__ . '/templates/explorer-view.html';
    
    echo file_get_contents(__DIR__ . '/templates/footer.html');

} else {
    
    // ----------------------------------------------------
    // FALL B: ICH BIN NICHT EINGELOGGT -> LOGIN FORMULAR
    // ----------------------------------------------------
    $header = file_get_contents(__DIR__ . '/templates/header.html');
    
    // Im Login verstecke ich die Navigation und den Profilstatus komplett
    echo str_replace('{{USER_STATUS}}', '', $header);
    
    // ==============================================================================
    // NICHT BEWERTUNGSRELEVANT: UX/UI Kür (Ghost Sounds Animation)
    // ==============================================================================
    // Im Login-Formular ist auch das Script für die herumschwebenden Geister eingebunden.
    $login_html = file_get_contents(__DIR__ . '/templates/login-form.html');
    
    // Falls ein Login-Fehler vorliegt, baue ich meine Error-Box passend zum Design ein
    $error_html = '';
    if (!empty($error_message)) {
        $error_html = '<div class="fl-window-error" style="color: #ff5555; background: #2a2a2a; border: 1px solid #ff5555; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace; font-size: 12px; border-radius: 3px;">' . htmlspecialchars($error_message) . '</div>';
    }
    
    // Den Fehler schleuse ich elegant direkt vor meinem <form>-Tag ein
    $login_html = str_replace('<form', $error_html . '<form', $login_html);
    
    echo $login_html;
    echo file_get_contents(__DIR__ . '/templates/footer.html');
}
?>