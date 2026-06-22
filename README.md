# 🎛️ WAV-Manager // Hoodtrap Edition

Ein webbasierter Datei-Explorer und Metadaten-Editor für Audio-Samples (Tracks, Stems, One-Shots). Das UI ist inspiriert vom dunklen, fokussierten Workflow gängiger DAWs wie FL Studio.

## 🚀 Features & Erweiterte Architektur
- **DAW-Inspirierter Sample Browser:** Übersichtliche Explorer-Ansicht, optimiert für schnelles Sifting im Studio.
- **Relationales Audio-Datenmodell:** Saubere datenbankseitige Trennung zwischen unterschiedlichen Audiotypen:
  - `songs` (Tracks / Full Mixes) mit BPM, Key und Tagging.
  - `samples` (Stems / Loops) mit BPM, Key und Herkunfts-Dokumentation.
  - `one_shots` (Drums / FX) als leichtgewichtige, pure Audio-Entitäten.
- **Flexible n:m Sound-Relationen:** Über eine relationale Zuordnungstabelle können One-Shots und Samples nahtlos ihren übergeordneten Stems oder Tracks zugewiesen werden.
- **Intelligente Live-Suche (Asynchron):** Kombinierte Datenbankabfragen via `UNION ALL` mit intelligentem Hashtag-Parser (`#trap`, `#drill`) und granularen Filtern für BPM-Bereiche und Tonarten.
- **Robustes Backend & Zeichenkonsistenz:** Striktes UTF-8-Handling (`utf8mb4_unicode_ci`) zur fehlerfreien Verarbeitung von Sonderzeichen in Sample-Namen sowie atomare Datenbank-Transaktionen beim Datei-Upload.
- **Security First:** Umfassender Schutz vor SQL-Injections durch konsistenten Einsatz von Prepared Statements (PDO) und ein geschütztes Login-System.

## 🛠️ Installation & Setup (Lokal)
1. Repository klonen und im Webroot-Verzeichnis deines lokalen Servers (z. B. `htdocs` bei XAMPP) ablegen.
2. Datenbank via phpMyAdmin anlegen (Name: `wav_manager`).
3. Die Tabellenstrukturen laut aktuellem Schema anlegen (Tabellen: `users`, `songs`, `samples`, `one_shots`, `sound_relations`).
4. Die Datei `src/config/db.php` mit den lokalen Datenbank-Zugangsdaten anpassen.
5. Das Projekt im Browser aufrufen: `http://localhost/wav-manager/src/`