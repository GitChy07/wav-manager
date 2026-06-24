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

## 🛠️ Installationsanleitung

Diese detaillierte Anleitung beschreibt die vollständige lokale Inbetriebnahme des WAV-Managers.

### 1. Projekt-Setup
1. **Webserver starten:** Stelle sicher, dass XAMPP (Apache & MySQL) läuft.
2. **Klonen:** Klone dieses Repository in dein lokales Webroot-Verzeichnis (z. B. `C:\xampp\htdocs\wav-manager`).
3. **Upload-Ordner:** Stelle sicher, dass im Projekt-Root das Verzeichnis `uploads/` existiert und vom Webserver beschrieben werden darf (Lese- und Schreibrechte für PHP).

### 2. Datenbank-Setup
1. Öffne phpMyAdmin (`http://localhost/phpmyadmin`).
2. Importiere das beiliegende SQL-Skript `database/wav_manager.sql`. 
   *(Dieses Skript erstellt automatisch die Datenbank `wav_manager`, alle benötigten Tabellen (inkl. `sound_relations`) sowie Demo-Daten und einen Test-User).*
3. **Sicherheitshinweis:** Für den Produktivbetrieb solltest du in MySQL einen dedizierten Applikations-User anlegen (z.B. `wav_app_user`), der ausschliesslich **DML-Rechte** (`SELECT, INSERT, UPDATE, DELETE`) auf diese Datenbank besitzt.

### 3. Konfiguration & Start
1. Kopiere die Datei `src/config/db.example.php` und benenne sie um in `src/config/db.php`.
2. Trage in der `src/config/db.php` deine lokalen MySQL-Verbindungsdaten (Host, User, Passwort) ein.
3. Öffne den Browser und rufe die Startseite auf:
   👉 `http://localhost/wav-manager/src/index.php`

---

## 📌 Architektur-Hinweis: W3C Validierung

Alle Ansichten dieses Projekts sind strikt valide nach **W3C-Standards** aufgebaut. 

**Hinweis für Entwickler:** Dateien im `src/templates/` Ordner wie `editor-view.html` oder `explorer-view.html` sind reine **Partials (Teilstücke)**. Sie enthalten keinen eigenen `<head>` oder `<title>`, da das gesamte Layout dynamisch durch PHP aus drei Teilen (`header.html` + `view` + `footer.html`) zusammengesetzt wird. 

Um das korrekte, finale HTML zu validieren, muss das Projekt im Browser aufgerufen und der generierte **Seitenquelltext** verwendet werden.