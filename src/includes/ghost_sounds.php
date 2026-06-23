<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

try {
    // Hole 20 zufällige Sounds aus allen 3 Tabellen, unabhängig vom User
    $query = "
        SELECT title, file_path, 'one_shot' as type FROM one_shots 
        UNION ALL 
        SELECT title, file_path, 'sample' as type FROM samples 
        UNION ALL 
        SELECT title, file_path, 'song' as type FROM songs 
        ORDER BY RAND() LIMIT 20
    ";
    
    $stmt = $pdo->query($query);
    $sounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'sounds' => $sounds]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
