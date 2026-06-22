<?php
// includes/update_sound.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

if ($id === 0 || empty($type) || empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Fehlende Basisdaten (ID, Typ oder Titel)']);
    exit;
}

try {
    if ($type === 'one_shot') {
        $stmt = $pdo->prepare("UPDATE one_shots SET title = :title WHERE id = :id");
        $stmt->execute([':title' => $title, ':id' => $id]);
        
    } elseif ($type === 'sample') {
        $bpm = isset($_POST['bpm']) ? (int)$_POST['bpm'] : null;
        $key = isset($_POST['key']) ? trim($_POST['key']) : null;
        $source = isset($_POST['source_description']) ? trim($_POST['source_description']) : '';
        
        $stmt = $pdo->prepare("UPDATE samples SET title = :title, bpm = :bpm, music_key = :key, source_description = :source WHERE id = :id");
        $stmt->execute([':title' => $title, ':bpm' => $bpm, ':key' => $key, ':source' => $source, ':id' => $id]);
        
    } elseif ($type === 'song') {
        $bpm = isset($_POST['bpm']) ? (int)$_POST['bpm'] : null;
        $key = isset($_POST['key']) ? trim($_POST['key']) : null;
        $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
        
        $stmt = $pdo->prepare("UPDATE songs SET title = :title, bpm = :bpm, music_key = :key, tags = :tags WHERE id = :id");
        $stmt->execute([':title' => $title, ':bpm' => $bpm, ':key' => $key, ':tags' => $tags, ':id' => $id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>