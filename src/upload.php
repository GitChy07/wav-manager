<?php
// src/upload.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $bpm = filter_input(INPUT_POST, 'bpm', FILTER_VALIDATE_INT);
    $music_key = trim($_POST['music_key'] ?? '');
    $source_description = trim($_POST['source_description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $target_sound = $_POST['target_sound'] ?? ''; // Format aus Dropdown: "song_12" oder "sample_5"
    
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($type) || !isset($_FILES['wav_file']) || $_FILES['wav_file']['error'] !== 0) {
        $error_message = 'Bitte wähle eine gültige WAV-Datei und einen Typ aus.';
    } 
    elseif ($type === 'sample' && empty($bpm)) {
        $error_message = 'Fehler: Für Samples musst du zwingend eine BPM-Zahl angeben.';
    } 
    else {
        $file = $_FILES['wav_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $auto_title = pathinfo($fileName, PATHINFO_FILENAME);

        if ($fileExtension !== 'wav') {
            $error_message = 'Es sind ausschliesslich echte .wav Dateien erlaubt!';
        } else {
            $newFileName = uniqid('wav_', true) . '.' . $fileExtension;
            $uploadDirectory = __DIR__ . '/../uploads/';
            $destination = $uploadDirectory . $newFileName;
            $relativePath = 'uploads/' . $newFileName; 

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            if (move_uploaded_file($fileTmpName, $destination)) {
                try {
                    // Start einer Transaction, damit Datei-Eintrag & Relation atomar sind
                    $pdo->beginTransaction();
                    $new_id = null;

                    if ($type === 'one_shot') {
                        $stmt = $pdo->prepare("INSERT INTO one_shots (user_id, title, description, file_path) VALUES (:user_id, :title, :description, :file_path)");
                        $stmt->execute([
                            'user_id' => $user_id,
                            'title' => $auto_title,
                            'description' => !empty($description) ? $description : null,
                            'file_path' => $relativePath
                        ]);
                        $new_id = $pdo->lastInsertId();
                        $child_type = 'one_shot';
                    } 
                    elseif ($type === 'sample') {
                        $stmt = $pdo->prepare("INSERT INTO samples (user_id, title, description, bpm, music_key, source_description, file_path) VALUES (:user_id, :title, :description, :bpm, :music_key, :source_description, :file_path)");
                        $stmt->execute([
                            'user_id' => $user_id,
                            'title' => $auto_title,
                            'description' => !empty($description) ? $description : null,
                            'bpm' => $bpm, 
                            'music_key' => !empty($music_key) ? $music_key : null,
                            'source_description' => !empty($source_description) ? $source_description : null,
                            'file_path' => $relativePath
                        ]);
                        $new_id = $pdo->lastInsertId();
                        $child_type = 'sample';
                    } 
                    elseif ($type === 'song') {
                        $stmt = $pdo->prepare("INSERT INTO songs (user_id, title, description, bpm, music_key, tags, file_path) VALUES (:user_id, :title, :description, :bpm, :music_key, :tags, :file_path)");
                        $stmt->execute([
                            'user_id' => $user_id,
                            'title' => $auto_title,
                            'description' => !empty($description) ? $description : null,
                            'bpm' => $bpm ? $bpm : null,
                            'music_key' => !empty($music_key) ? $music_key : null,
                            'tags' => !empty($tags) ? $tags : null,
                            'file_path' => $relativePath
                        ]);
                    }

                    // Flexible Verknüpfung in die Relationstabelle schreiben (falls gesetzt)
                    if ($new_id && !empty($target_sound) && strpos($target_sound, '_') !== false) {
                        list($parent_type, $parent_id) = explode('_', $target_sound);
                        
                        $stmtRel = $pdo->prepare("INSERT INTO sound_relations (parent_type, parent_id, child_type, child_id) VALUES (:parent_type, :parent_id, :child_type, :child_id)");
                        $stmtRel->execute([
                            'parent_type' => $parent_type,
                            'parent_id' => (int)$parent_id,
                            'child_type' => $child_type,
                            'child_id' => $new_id
                        ]);
                    }

                    $pdo->commit();
                    $success_message = 'WAV-File "' . htmlspecialchars($auto_title) . '" erfolgreich als ' . strtoupper($type) . ' hochgeladen!';
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error_message = 'Datenbankfehler: ' . $e->getMessage();
                }
            } else {
                $error_message = 'Fehler beim Speichern der Datei im Root-Verzeichnis.';
            }
        }
    }
}

// ----------------------------------------------------
// DB-QUERY: OPTIONEN FÜR DIE RELATIONS-AUSWAHL BAUEN
// ----------------------------------------------------
$relation_options_html = '<option value="">-- Standalone (No Parent) --</option>';
try {
    // 1. Songs holen (Mögliche Parents für Samples und One-Shots)
    $stmtSongs = $pdo->query("SELECT id, title FROM songs ORDER BY title ASC");
    if ($stmtSongs->rowCount() > 0) {
        $relation_options_html .= '<optgroup id="optgroup-songs" label="🎵 Tracks / Full Mixes">';
        while ($row = $stmtSongs->fetch()) {
            $relation_options_html .= '<option value="song_' . $row['id'] . '"> ' . htmlspecialchars($row['title']) . '</option>';
        }
        $relation_options_html .= '</optgroup>';
    }

    // 2. Samples holen (Mögliche Parents NUR für One-Shots)
    $stmtSamples = $pdo->query("SELECT id, title FROM samples ORDER BY title ASC");
    if ($stmtSamples->rowCount() > 0) {
        $relation_options_html .= '<optgroup id="optgroup-samples" label="🎹 Stems / Samples">';
        while ($row = $stmtSamples->fetch()) {
            $relation_options_html .= '<option value="sample_' . $row['id'] . '"> ' . htmlspecialchars($row['title']) . '</option>';
        }
        $relation_options_html .= '</optgroup>';
    }
} catch (PDOException $e) {
    $relation_options_html = '<option value="">Fehler beim Laden der Relationen</option>';
}

// ----------------------------------------------------
// PERSISTENTER LAYOUT-AUFBAU FÜR MEIN STUDIO
// ----------------------------------------------------
$header = file_get_contents(__DIR__ . '/templates/header.html');
$user_status_html = '<a href="index.php" style="font-weight: bold; color: var(--text-muted); margin-right: 20px; text-decoration: none;">&lt; Back to Playlist</a>' .
                    '<span style="color: var(--text-muted); margin-right: 15px;">Producer: <strong style="color: var(--text-light);">' . htmlspecialchars($_SESSION['username']) . '</strong></span>' .
                    '<a href="profile.php" style="color: #4facfe; text-decoration: none; margin: 0 10px;">[SETTINGS]</a>' .
                    '<a href="index.php?logout=1" style="text-decoration: none;">[LOGOUT]</a>';
                    
echo str_replace('{{USER_STATUS}}', $user_status_html, $header);

$upload_html = file_get_contents(__DIR__ . '/templates/upload-form.html');
$error_html = !empty($error_message) ? '<div class="msg msg-error">' . htmlspecialchars($error_message) . '</div>' : '';
$success_html = !empty($success_message) ? '<div class="msg msg-success">' . htmlspecialchars($success_message) . '</div>' : '';

// Ersetzungen im Template vornehmen
$upload_html = str_replace('{{ERROR}}', $error_html, $upload_html);
$upload_html = str_replace('{{SUCCESS}}', $success_html, $upload_html);
$upload_html = str_replace('{{RELATION_OPTIONS}}', $relation_options_html, $upload_html); // Optionen injizieren

echo $upload_html;
echo file_get_contents(__DIR__ . '/templates/footer.html');
?>