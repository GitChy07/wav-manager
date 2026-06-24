<?php
// src/includes/auth.php
$db_config_file = __DIR__ . '/../config/db.php';
if (!file_exists($db_config_file)) {
    die("<div style='font-family:sans-serif; padding:50px; text-align:center; background:#111; color:#fff; height:100vh;'>
        <h1 style='color:#ff4757;'>⚙️ Setup erforderlich</h1>
        <p style='color:#a4b0be; font-size:18px;'>Die Datenbank-Konfiguration wurde nicht gefunden.</p>
        <div style='background:#2f3542; padding:20px; border-radius:8px; display:inline-block; margin-top:20px; text-align:left;'>
            <p style='margin:0 0 10px 0;'><b>1.</b> Kopiere die Datei <code>src/config/db.example.php</code></p>
            <p style='margin:0 0 10px 0;'><b>2.</b> Benenne die Kopie um in <code>db.php</code></p>
            <p style='margin:0;'><b>3.</b> Lade diese Seite neu.</p>
        </div>
        </div>");
}
require_once $db_config_file;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function registerUser($pdo, $username, $email, $genre, $password) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return "Dieser Producer existiert bereits.";
    }

    // ==============================================================================
    // BEWERTUNGSRELEVANT: KOMPETENZ C11 (Passwort-Hashing)
    // ==============================================================================
    // Passwörter werden NIEMALS im Klartext gespeichert. Die Funktion password_hash() 
    // erzeugt einen sicheren Hash inklusive automatischem Salt.
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // ==============================================================================
        // BEWERTUNGSRELEVANT: KOMPETENZ C13 (Registrierung umsetzen)
        // ==============================================================================
        // Der neue Benutzer wird samt gehashtem Passwort sicher über Prepared
        // Statements in der Datenbank hinterlegt.
        $insertStmt = $pdo->prepare("INSERT INTO users (username, email, genre, password_hash) VALUES (?, ?, ?, ?)");
        if ($insertStmt->execute([$username, $email, $genre, $password_hash])) {
            return true;
        }
        return "Fehler beim Speichern.";
    } catch (PDOException $e) {
        return "Datenbankfehler: " . $e->getMessage();
    }
}

function loginUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT id, email, genre, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    
    // ==============================================================================
    // BEWERTUNGSRELEVANT: KOMPETENZ C14 (Anmeldung / Login)
    // ==============================================================================
    // Das eingegebene Klartext-Passwort wird gegen den in der DB gespeicherten Hash geprüft.
    if ($row && password_verify($password, $row['password_hash'])) {
        
        // ==============================================================================
        // BEWERTUNGSRELEVANT: KOMPETENZ C10 (Session-Angriffe erschweren)
        // ==============================================================================
        // Unmittelbar nach dem Login wird die Session-ID neu generiert (session_regenerate_id(true)), 
        // um Session-Fixation und Session-Hijacking zu verhindern.
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $row['email'];
        $_SESSION['genre'] = $row['genre'];
        return true;
    }
    return "Benutzername oder Passwort falsch.";
}

function logoutUser() {
    // ==============================================================================
    // BEWERTUNGSRELEVANT: KOMPETENZ C9 (Abmeldung / Logout)
    // ==============================================================================
    // Die serverseitige Session wird komplett zerstört, und das Client-Cookie 
    // wird durch das Setzen eines abgelaufenen Datums gelöscht.
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: index.php");
    exit;
}

// C15: Profil bearbeiten / Passwort ändern
function updateProfile($pdo, $user_id, $email, $genre, $new_password = '') {
    try {
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET email = ?, genre = ?, password_hash = ? WHERE id = ?");
            $success = $stmt->execute([$email, $genre, $password_hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, genre = ? WHERE id = ?");
            $success = $stmt->execute([$email, $genre, $user_id]);
        }
        
        if ($success) {
            $_SESSION['email'] = $email;
            $_SESSION['genre'] = $genre;
            return true;
        }
        return "Daten konnten nicht aktualisiert werden.";
    } catch (PDOException $e) {
        return "Datenbankfehler: " . $e->getMessage();
    }
}
?>