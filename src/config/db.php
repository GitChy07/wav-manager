<?php
// src/config/db.php

$host = '127.0.0.1'; // IP-Adresse statt localhost (sicherer bei XAMPP)
$dbname = 'wav_manager';
$username = 'root';  // Wir nutzen den Standard-Admin
$password = '';      // Bei XAMPP standardmäßig leer

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