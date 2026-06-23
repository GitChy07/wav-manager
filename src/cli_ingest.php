<?php
require_once __DIR__ . '/config/db.php';

$jsonFile = __DIR__ . '/ingest_files.json';
if (!file_exists($jsonFile)) {
    die("JSON file not found.");
}

$json = file_get_contents($jsonFile);
$files = json_decode($json, true);

// Wir nehmen einfach den ersten registrierten User (vermutlich dich!)
$userId = 1; 
$stmt = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
if ($row = $stmt->fetch()) {
    $userId = $row['id'];
}

$counts = ['song' => 0, 'sample' => 0, 'one_shot' => 0];

foreach($files as $f) {
    $type = $f['Type'];
    $name = $f['Name'];
    $sourcePath = $f['FullName'];
    
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    // Ersetze unschöne Unterstriche im Titel für die Anzeige
    $basename = str_replace('_', ' ', pathinfo($name, PATHINFO_FILENAME));
    
    $newFileName = uniqid('wav_', true) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $destPath = $uploadDir . $newFileName;
    $relativePath = 'uploads/' . $newFileName;
    
    // Copy the file
    if (copy($sourcePath, $destPath)) {
        if ($type === 'song') {
            // Song (Master)
            $stmt = $pdo->prepare("INSERT INTO songs (user_id, title, tags, file_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $basename, '#master #auto', $relativePath]);
            $counts['song']++;
            
        } elseif ($type === 'sample') {
            // Sample
            $bpm = 120; // Default
            // Versuche BPM aus dem Dateinamen zu lesen (z.B. "130bpm" oder "_130_")
            if (preg_match('/(\d{2,3})\s*bpm/i', $name, $m)) {
                $bpm = (int)$m[1];
            } elseif (preg_match('/_(\d{2,3})_/', $name, $m)) {
                $bpm = (int)$m[1];
            }
            $stmt = $pdo->prepare("INSERT INTO samples (user_id, title, bpm, source_description, file_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $basename, $bpm, 'Auto-Ingest from FL Studio', $relativePath]);
            $counts['sample']++;
            
        } else {
            // One-Shot
            $stmt = $pdo->prepare("INSERT INTO one_shots (user_id, title, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $basename, $relativePath]);
            $counts['one_shot']++;
        }
    }
}

echo "Upload erfolgreich abgeschlossen!\n";
echo "Songs: " . $counts['song'] . "\n";
echo "Samples: " . $counts['sample'] . "\n";
echo "One-Shots: " . $counts['one_shot'] . "\n";
@unlink($jsonFile); // Clean up
?>
