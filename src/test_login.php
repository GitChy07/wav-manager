<?php
// Binde unsere sichere Datenbankverbindung ein
require_once __DIR__ . '/config/db.php';

$msg = "";

try {
    // 1. Test-User anlegen (falls er noch nicht existiert)
    // Dies testet unser INSERT-Recht und das Passwort-Hashing (Kompetenz C11)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'test'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $hash = password_hash('1234', PASSWORD_DEFAULT); // Sicheres Hashing!
        $insert = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES ('test', ?)");
        $insert->execute([$hash]);
        $msg = "Test-User 'test' (Passwort: 1234) wurde erfolgreich in der Datenbank angelegt!";
    }

    // 2. Login-Logik verarbeiten (Kompetenz C14)
    $login_result = "";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        
        // Hole den gehashten Wert aus der DB
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $row = $stmt->fetch();
        
        // Verifiziere das Klartext-Passwort gegen den Hash
        if ($row && password_verify($pass, $row['password_hash'])) {
            $login_result = "<div style='color:green; font-weight:bold; padding: 10px; border: 1px solid green;'>✅ Verbindung & Login ERFOLGREICH!<br>Die PDO-Verbindung steht und password_verify() funktioniert.</div>";
        } else {
            $login_result = "<div style='color:red; font-weight:bold; padding: 10px; border: 1px solid red;'>❌ Login fehlgeschlagen. Falscher Benutzer oder Passwort.</div>";
        }
    }

} catch (PDOException $e) {
    // Falls die Verbindung oder Query fehlschlägt, sehen wir es hier
    $login_result = "<div style='color:red; font-weight:bold; padding: 10px; border: 1px solid red;'>❌ Datenbankfehler: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>WAV-Manager - Test Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>System-Test</h2>
        <p>Prüft die Datenbankverbindung und den Passwort-Hash Mechanismus.</p>
        
        <?php if(!empty($msg)) echo "<p style='color:blue;'>ℹ️ $msg</p>"; ?>
        
        <form method="post">
            <label>Benutzername:</label>
            <input type="text" name="username" value="test" required>
            
            <label>Passwort:</label>
            <input type="password" name="password" value="1234" required>
            
            <button type="submit">Login testen</button>
        </form>
        <br>
        <?= $login_result ?>
    </div>
</body>
</html>
