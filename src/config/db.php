<?php
// src/config/db.php

$host = 'localhost'; // Muss mit dem 'localhost' beim GRANT übereinstimmen
$dbname = 'wav_manager';
$username = 'wav_app_user'; // Spezieller User für die Web-App (C12: Eingeschränkte Rechte)
$password = 'wav_secure_pass'; // Passwort für diesen User

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Wenn du das im Browser siehst, läuft alles!
    // echo "Verbindung erfolgreich!"; 
    
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>