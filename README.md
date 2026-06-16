# 🎛️ WAV-Manager // Hoodtrap Edition

Ein webbasierter Datei-Explorer und Metadaten-Editor für Audio-Samples (Tracks, Stems, One-Shots). Das UI ist inspiriert vom dunklen Workflow gängiger DAWs wie FL Studio.

## Features
- **Sample Browser:** Übersichtliche Explorer-Ansicht für alle Audiodateien.
- **Attribut-Editor:** BPM, Tonart (Key) und Tags direkt im Browser anpassen.
- **Sicherheit:** Login-System und geschützte Datenbankabfragen (Prepared Statements).
- **Struktur:** Saubere Trennung von Logik (PHP) und Design (HTML/CSS).

## Installation (Lokal)
1. Repository klonen und in den `htdocs` Ordner von XAMPP/MAMP legen.
2. Datenbank über phpMyAdmin anlegen (Tabellen: `users`, `sounds`, `track_relations`).
3. Die Datei `src/config/db.php` mit den lokalen Datenbank-Zugangsdaten anpassen.
4. Im Browser öffnen: `http://localhost/wav-manager/src/`