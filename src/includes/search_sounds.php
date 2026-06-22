<?php
// includes/search_sounds.php
header('Content-Type: application/json');

/**
 * 1. ZENTRALE DATENBANK-VERBINDUNG EINBINDEN
 */
$dbPath = __DIR__ . '/../config/db.php';

if (file_exists($dbPath)) {
    require_once $dbPath; 
} else {
    echo json_encode(["error" => "Konfigurationsdatei db.php nicht gefunden."]);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode(["error" => "PDO-Instanz wurde nicht korrekt übergeben."]);
    exit;
}

// 2. GET-PARAMETER PARSEN
$searchRaw = isset($_GET['search']) ? trim($_GET['search']) : '';
$type      = isset($_GET['type']) ? trim($_GET['type']) : '';
$key       = isset($_GET['key']) ? trim($_GET['key']) : '';
$bpmMin    = isset($_GET['bpm_min']) ? (int)$_GET['bpm_min'] : 60;
$bpmMax    = isset($_GET['bpm_max']) ? (int)$_GET['bpm_max'] : 200;

$queries = [];
$params = [];

// Hashtags und Freitext extrahieren
$pureText = $searchRaw;
$tags = [];
if (!empty($searchRaw)) {
    preg_match_all('/#[a-zA-Z0-9_-]+/', $searchRaw, $matches);
    $tags = $matches[0];
    $pureText = trim(preg_replace('/#[a-zA-Z0-9_-]+/', '', $searchRaw));
}

/**
 * 3. SUB-QUERY: ONE-SHOTS
 * Werden geladen, wenn kein Typ oder explizit 'one_shot' gewählt ist.
 * BPM- und Key-Filter werden hier ignoriert.
 */
if (empty($type) || $type === 'one_shot') {
    $oneShotFilter = "";
    if (!empty($pureText)) {
        $oneShotFilter .= " AND (title LIKE :text_os OR description LIKE :text_os)";
        $params[':text_os'] = '%' . $pureText . '%';
    }
    if (!empty($tags)) {
        foreach ($tags as $index => $tag) {
            $cleanTag = str_replace('#', '', $tag);
            $oneShotFilter .= " AND (description LIKE :tag_os_$index)";
            $params[":tag_os_$index"] = '%' . $cleanTag . '%';
        }
    }
    $queries[] = "SELECT id, title, description, 'one_shot' AS type, NULL AS bpm, NULL AS music_key, NULL AS source_description, NULL AS tags, file_path 
                  FROM one_shots WHERE 1=1" . $oneShotFilter;
}

/**
 * 4. SUB-QUERY: SAMPLES
 * Tonart und BPM greifen laut Original-Logik nur, wenn der Typ explizit gesetzt ist.
 */
if (empty($type) || $type === 'sample') {
    $sampleFilter = "";
    if (!empty($pureText)) {
        $sampleFilter .= " AND (title LIKE :text_sample OR description LIKE :text_sample)";
        $params[':text_sample'] = '%' . $pureText . '%';
    }
    if (!empty($tags)) {
        foreach ($tags as $index => $tag) {
            $cleanTag = str_replace('#', '', $tag);
            $sampleFilter .= " AND (description LIKE :tag_sample_$index)";
            $params[":tag_sample_$index"] = '%' . $cleanTag . '%';
        }
    }
    if ($type === 'sample' || $type === 'song') {
        if (!empty($key)) {
            $sampleFilter .= " AND LOWER(music_key) = :key_sample";
            $params[':key_sample'] = strtolower($key);
        }
        $sampleFilter .= " AND bpm BETWEEN :bpm_min_sample AND :bpm_max_sample";
        $params[':bpm_min_sample'] = $bpmMin;
        $params[':bpm_max_sample'] = $bpmMax;
    }
    $queries[] = "SELECT id, title, description, 'sample' AS type, bpm, music_key, source_description, NULL AS tags, file_path 
                  FROM samples WHERE 1=1" . $sampleFilter;
}

/**
 * 5. SUB-QUERY: SONGS
 */
if (empty($type) || $type === 'song') {
    $songFilter = "";
    if (!empty($pureText)) {
        $songFilter .= " AND (title LIKE :text_song OR description LIKE :text_song)";
        $params[':text_song'] = '%' . $pureText . '%';
    }
    if (!empty($tags)) {
        foreach ($tags as $index => $tag) {
            $cleanTag = str_replace('#', '', $tag);
            $songFilter .= " AND (tags LIKE :tag_song_$index OR description LIKE :tag_song_$index)";
            $params[":tag_song_$index"] = '%' . $cleanTag . '%';
        }
    }
    if ($type === 'sample' || $type === 'song') {
        if (!empty($key)) {
            $songFilter .= " AND LOWER(music_key) = :key_song";
            $params[':key_song'] = strtolower($key);
        }
        $songFilter .= " AND bpm BETWEEN :bpm_min_song AND :bpm_max_song";
        $params[':bpm_min_song'] = $bpmMin;
        $params[':bpm_max_song'] = $bpmMax;
    }
    $queries[] = "SELECT id, title, description, 'song' AS type, bpm, music_key, NULL AS source_description, tags, file_path 
                  FROM songs WHERE 1=1" . $songFilter;
}

// 6. ALLE QUERIES MIT UNION VERBINDEN & SORTIEREN (Wie in deiner index.php über ID)
$sql = implode(" UNION ALL ", $queries) . " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(["error" => "Abfragefehler in der Audio-Engine: " . $e->getMessage()]);
}