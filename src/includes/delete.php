<?php
// src/includes/delete.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sound_id = intval($_POST['sound_id'] ?? 0);
$type = $_POST['type'] ?? '';

$allowed_types = ['one_shot' => 'one_shots', 'sample' => 'samples', 'song' => 'songs'];

if (array_key_exists($type, $allowed_types) && $sound_id > 0) {
    $table = $allowed_types[$type];
    
    try {
        // Pfad holen für physisches Löschen
        $stmt = $pdo->prepare("SELECT file_path FROM $table WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $sound_id, 'user_id' => $user_id]);
        $sound = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sound) {
            // Wichtig: Da wir in src/includes/ sind, müssen wir 2 Ebenen hoch (../../), 
            // um in den globalen uploads/ Ordner zu kommen
            $physical_path = __DIR__ . '/../../' . $sound['file_path'];
            if (file_exists($physical_path)) {
                unlink($physical_path);
            }
            
            // DB Eintrag löschen
            $delete_stmt = $pdo->prepare("DELETE FROM $table WHERE id = :id AND user_id = :user_id");
            $delete_stmt->execute(['id' => $sound_id, 'user_id' => $user_id]);
        }
    } catch (PDOException $e) {
        // Silent fail oder Logging
    }
}

header('Location: ../index.php');
exit;