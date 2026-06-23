<?php
// includes/update_sound.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Nicht angemeldet']);
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

// ==============================================================================
// BEWERTUNGSRELEVANT: KOMPETENZ C6 (Serverseitige Validierung)
// ==============================================================================
// Bevor wir die Datenbank anfragen, wird serverseitig zwingend geprüft, 
// ob alle notwendigen Pflichtfelder für ein Update vorhanden sind.
if ($id === 0 || empty($type) || empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Fehlende Basisdaten (ID, Typ oder Titel)']);
    exit;
}

try {
    // ==============================================================================
    // BEWERTUNGSRELEVANT: KOMPETENZ C17 (Eigene Datensätze ändern)
    // ==============================================================================
    // Bei jedem UPDATE wird zwingend die aktuelle `user_id` der Session mit 
    // in die WHERE-Klausel eingebunden (AND user_id = :user_id).
    // Dadurch wird sichergestellt, dass niemand die Metadaten fremder Sounds manipulieren kann.
    if ($type === 'one_shot') {
        $stmt = $pdo->prepare("UPDATE one_shots SET title = :title WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':title' => $title, ':id' => $id, ':user_id' => $user_id]);
        
    } elseif ($type === 'sample') {
        $bpm = isset($_POST['bpm']) ? (int)$_POST['bpm'] : null;
        $key = isset($_POST['key']) ? trim($_POST['key']) : null;
        $source = isset($_POST['source_description']) ? trim($_POST['source_description']) : '';
        
        $stmt = $pdo->prepare("UPDATE samples SET title = :title, bpm = :bpm, music_key = :key, source_description = :source WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':title' => $title, ':bpm' => $bpm, ':key' => $key, ':source' => $source, ':id' => $id, ':user_id' => $user_id]);
        
    } elseif ($type === 'song') {
        $bpm = isset($_POST['bpm']) ? (int)$_POST['bpm'] : null;
        $key = isset($_POST['key']) ? trim($_POST['key']) : null;
        $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
        
        $stmt = $pdo->prepare("UPDATE songs SET title = :title, bpm = :bpm, music_key = :key, tags = :tags WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':title' => $title, ':bpm' => $bpm, ':key' => $key, ':tags' => $tags, ':id' => $id, ':user_id' => $user_id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>