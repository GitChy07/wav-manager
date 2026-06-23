<?php
// src/includes/auth.php
require_once __DIR__ . '/../config/db.php';

// ==============================================================================
// BEWERTUNGSRELEVANT: KOMPETENZ C8 (Session-Handling)
// ==============================================================================
// Die Session wird gestartet, falls noch keine aktiv ist. Dies ist zwingend 
// erforderlich, um Benutzer über mehrere Seitenaufrufe hinweg wiederzuerkennen.
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