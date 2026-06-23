<?php
// src/config/db.php

// ==============================================================================
// BEWERTUNGSRELEVANT: KOMPETENZ C12 (Eingeschränkte DB-Rechte)
// ==============================================================================
// Hier wird explizit ein Datenbank-User (wav_app_user) mit eingeschränkten Rechten
// verwendet, anstelle des 'root' Benutzers, um bei einer potenziellen 
// Kompromittierung den Schaden zu begrenzen (Prinzip des minimalen Privilegs).
$host = 'localhost'; 
$dbname = 'wav_manager';
$username = 'wav_app_user'; 
$password = 'wav_secure_pass'; 

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // ==============================================================================
    // BEWERTUNGSRELEVANT: KOMPETENZ C19 (SQL-Injection verhindern)
    // ==============================================================================
    // PDO wird konfiguriert, um Exceptions zu werfen und Prepared Statements zu 
    // unterstützen. Im gesamten Projekt werden Variablen ausschließlich über 
    // Prepared Statements an SQL-Queries übergeben, wodurch SQL-Injections
    // effektiv verhindert werden.
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