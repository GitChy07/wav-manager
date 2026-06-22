<?php
// includes/relations.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$one_shot_id = isset($_POST['one_shot_id']) ? (int)$_POST['one_shot_id'] : 0;
$target_raw = isset($_POST['target_sound']) ? $_POST['target_sound'] : '';

if ($one_shot_id === 0 || empty($target_raw)) {
    echo json_encode(['success' => false, 'error' => 'Fehlende Zuordnungsdaten.']);
    exit;
}

$parts = explode('_', $target_raw);
if (count($parts) !== 2) {
    echo json_encode(['success' => false, 'error' => 'Ungültiges Ziel-Format.']);
    exit;
}

$parent_type = $parts[0];
$parent_id = (int)$parts[1];
$child_type = 'one_shot'; // Da es aus relations.php kommt, ist das Child hier ein One-Shot

try {
    // Prüfen nach neuem Schema parent/child
    $check = $pdo->prepare("SELECT id FROM sound_relations WHERE parent_type = :p_type AND parent_id = :p_id AND child_type = :c_type AND child_id = :c_id");
    $check->execute([
        ':p_type' => $parent_type,
        ':p_id'   => $parent_id,
        ':c_type' => $child_type,
        ':c_id'   => $one_shot_id
    ]);
    
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Diese Verknüpfung existiert bereits.']);
        exit;
    }

    // Relation einfügen nach neuem Schema
    $stmt = $pdo->prepare("INSERT INTO sound_relations (parent_type, parent_id, child_type, child_id) VALUES (:p_type, :p_id, :c_type, :c_id)");
    $stmt->execute([
        ':p_type' => $parent_type,
        ':p_id'   => $parent_id,
        ':c_type' => $child_type,
        ':c_id'   => $one_shot_id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>