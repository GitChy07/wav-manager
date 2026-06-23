<?php
// src/profile.php
require_once __DIR__ . '/includes/auth.php';

// Wenn man nicht eingeloggt ist, ab zum Login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $password = $_POST['new_password'] ?? '';
    
    // Server-seitige Validierung
    if (empty($email)) {
        $error_message = "EMAIL IS REQUIRED";
    } elseif (!empty($password) && strlen($password) < 4) {
        $error_message = "NEW PASSWORD MUST BE AT LEAST 4 CHARS";
    } else {
        // ==============================================================================
        // BEWERTUNGSRELEVANT: KOMPETENZ C15 (Passwort ändern)
        // ==============================================================================
        // Die Funktion updateProfile validiert, ob ein neues Passwort gesetzt werden soll,
        // hasht dieses neu mit password_hash() und aktualisiert die DB-Einträge sicher.
        $result = updateProfile($pdo, $_SESSION['user_id'], $email, $genre, $password);
        if ($result === true) {
            $success_message = "PROFILE UPDATED SUCCESSFULLY";
        } else {
            $error_message = $result;
        }
    }
}

// Lade den Header und zeige den aktuellen Benutzernamen an
$header = file_get_contents(__DIR__ . '/templates/header.html');
$user_status_html = '<span>Producer: <strong>' . htmlspecialchars($_SESSION['username']) . '</strong></span>' .
                    '<a href="profile.php" style="color: #4facfe; text-decoration: none; margin: 0 10px;">[SETTINGS]</a>' .
                    '<a href="?logout=1" style="text-decoration: none;">[LOGOUT]</a>';
echo str_replace('{{USER_STATUS}}', $user_status_html, $header);
?>

<div class="fl-panel" style="margin: 20px; border: 1px solid #333; background: #1e1e1e;">
    <div class="fl-window-header">⚙️ PRODUCER SETTINGS</div>
    <div class="fl-window-body" style="padding: 20px;">
        
        <?php if (!empty($error_message)): ?>
            <div class="fl-window-error" style="color: #ff5555; background: #2a2a2a; border: 1px solid #ff5555; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace;">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="fl-window-success" style="color: #55ff55; background: #2a2a2a; border: 1px solid #55ff55; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace;">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" style="max-width: 400px; margin: 0 auto;">
            <div class="form-group" style="margin-bottom: 15px;">
                <!-- ============================================================================== -->
                <!-- BEWERTUNGSRELEVANT: KOMPETENZ C7 (Script-Injection / XSS Schutz)             -->
                <!-- ============================================================================== -->
                <!-- Die dynamische Datenausgabe aus der Session/Datenbank wird durch               -->
                <!-- htmlspecialchars() maskiert, sodass potenzieller JS-Code als Text dargestellt  -->
                <!-- und nicht im Browser ausgeführt wird.                                          -->
                <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">PRODUCER NAME</label>
                <!-- Benutzername kann hier aus Sicherheitsgründen nicht geändert werden -->
                <input type="text" class="fl-input" value="<?= htmlspecialchars($_SESSION['username']) ?>" disabled style="width:100%; padding:8px; background:#111; color:#777; border:1px solid #333;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">EMAIL</label>
                <input type="email" name="email" class="fl-input" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required style="width:100%; padding:8px; background:#2a2a2a; color:#fff; border:1px solid #444;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">FAVORITE GENRE</label>
                <input type="text" name="genre" class="fl-input" value="<?= htmlspecialchars($_SESSION['genre'] ?? '') ?>" style="width:100%; padding:8px; background:#2a2a2a; color:#fff; border:1px solid #444;">
            </div>
            
            <hr style="border:0; border-top:1px solid #333; margin: 20px 0;">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">NEW PASSWORD (leave blank to keep current)</label>
                <input type="password" name="new_password" class="fl-input" placeholder="Min. 4 chars" style="width:100%; padding:8px; background:#2a2a2a; color:#fff; border:1px solid #444;">
            </div>
            <button type="submit" class="fl-btn" style="width:100%; padding:10px; background:#4facfe; color:#fff; border:none; cursor:pointer; font-weight:bold;">UPDATE PROFILE</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color:#aaa; font-family:monospace; font-size:12px; text-decoration:none;">&lt; BACK TO STUDIO</a>
        </div>
    </div>
</div>

<?php
echo file_get_contents(__DIR__ . '/templates/footer.html');
?>
