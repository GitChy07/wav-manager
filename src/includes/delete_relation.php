<?php
// src/includes/delete_relation.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Nicht autorisiert.']);
    exit;
}

$parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
$parent_type = isset($_POST['parent_type']) ? $_POST['parent_type'] : '';
$child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;
$child_type = isset($_POST['child_type']) ? $_POST['child_type'] : '';

if ($parent_id === 0 || empty($parent_type) || $child_id === 0 || empty($child_type)) {
    echo json_encode(['success' => false, 'error' => 'Fehlende Daten zum Löschen der Verknüpfung.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM sound_relations WHERE parent_type = :pt AND parent_id = :pi AND child_type = :ct AND child_id = :ci");
    $stmt->execute([
        ':pt' => $parent_type,
        ':pi' => $parent_id,
        ':ct' => $child_type,
        ':ci' => $child_id
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
