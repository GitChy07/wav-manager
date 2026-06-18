<?php
// src/register.php
require_once __DIR__ . '/includes/auth.php';

// Falls schon eingeloggt, direkt zum Studio (index)
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "MISSING REQUIRED FIELDS";
    } elseif (strlen($password) < 4) {
        $error_message = "PASSWORD MUST BE AT LEAST 4 CHARS";
    } else {
        $result = registerUser($pdo, $username, $email, $genre, $password);
        if ($result === true) {
            $success_message = "PRODUCER ACCOUNT CREATED SUCCESSFULLY";
        } else {
            $error_message = $result;
        }
    }
}

// Lade den Header deines Designs
$header = file_get_contents(__DIR__ . '/templates/header.html');
echo str_replace('{{USER_STATUS}}', '', $header);
?>

<div class="login-wrapper">
    <div class="fl-window">
        <div class="fl-window-header">NEW PRODUCER REGISTRATION</div>
        <div class="fl-window-body">
            
            <?php if (!empty($error_message)): ?>
                <div class="fl-window-error" style="color: #ff5555; background: #2a2a2a; border: 1px solid #ff5555; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace; font-size: 12px; border-radius: 3px;">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="fl-window-success" style="color: #55ff55; background: #2a2a2a; border: 1px solid #55ff55; padding: 10px; margin-bottom: 15px; text-align: center; font-family: monospace; font-size: 12px; border-radius: 3px;">
                    <?= htmlspecialchars($success_message) ?>
                    <br><br>
                    <a href="index.php" style="color: #55ff55; text-decoration: underline;">&gt; CONNECT NOW</a>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label>PRODUCER NAME</label>
                        <input type="text" name="username" class="fl-input" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>EMAIL</label>
                        <input type="email" name="email" class="fl-input" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>FAVORITE GENRE</label>
                        <input type="text" name="genre" class="fl-input" placeholder="e.g. Trap, Lo-Fi, EDM" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>PASSWORD</label>
                        <input type="password" name="password" class="fl-input" required>
                    </div>
                    <button type="submit" class="fl-btn">CREATE ACCOUNT</button>
                </form>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="index.php" style="color: #888; font-family: monospace; font-size: 11px; text-decoration: none;">&lt; BACK TO LOGIN</a>
            </div>
        </div>
    </div>
</div>

<?php
// Lade den Footer deines Designs
echo file_get_contents(__DIR__ . '/templates/footer.html');
?>
