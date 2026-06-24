<?php
// src/config/db.php

// Datenbank-Konfiguration
// Aus Sicherheitsgründen wird ein dedizierter App-User mit eingeschränkten Rechten (DML only) verwendet.
$host = 'localhost'; 
$dbname = 'wav_manager';
$username = 'wav_app_user';
$password = 'SuperSecret123!';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // PDO Konfiguration für Prepared Statements und sicheres Error Handling
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>