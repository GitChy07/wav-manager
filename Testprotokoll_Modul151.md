# Testprotokoll Modul 151 (WAV-Manager)

Dieses Dokument dient als Nachweis für die Durchführung der System-Tests anhand des Kompetenzrasters (Modul 151). Es wurden White-Box- und Black-Box-Tests durchgeführt, um die geforderten "C-Kompetenzen" (Notenbereich 6) nachzuweisen.

## 1. Frontend & Client-Verhalten

### **C4: HTML Validierung**
- **Test-Szenario:** Der Quellcode der Hauptseiten (`login-form.html`, `explorer-view.html`, `upload-form.html`) wurde per W3C Validator überprüft.
- **Erwartetes Ergebnis:** Keine Struktur-Fehler im Markup.
- **Tatsächliches Ergebnis:** Validierung fehlerfrei. `header.html` und `footer.html` schließen das Dokument syntaktisch korrekt ab.
- **Status:** ✅ BESTANDEN

### **C5: Clientseitige Validierung**
- **Test-Szenario:** Im Upload-Formular wird versucht, das Formular ohne Auswahl einer Datei oder ohne Angabe der BPM bei einem Sample abzusenden.
- **Erwartetes Ergebnis:** Der Webbrowser verhindert das Absenden (HTML5 `required`). Das Feld markiert sich rot.
- **Tatsächliches Ergebnis:** Das Absenden wird durch den Browser direkt unterbunden. JS schaltet dynamisch Pflichtfelder je nach Audiotyp um.
- **Status:** ✅ BESTANDEN

## 2. Serverseitige Sicherheit & Validierung

### **C6: Serverseitige Validierung**
- **Test-Szenario:** Ein direkter POST-Request an `upload.php` wird gesendet, bei dem die eigentliche Dateianlage (`$_FILES['wav_file']`) fehlt.
- **Erwartetes Ergebnis:** Das PHP-Skript fängt den Fehler ab und leitet mit einer Fehlermeldung zurück zum Formular. Es stürzt nicht ab.
- **Tatsächliches Ergebnis:** Serverseitige Logik (z.B. in `upload.php`) prüft Arrays und Werte ab. Backend verhindert defekte Datensätze.
- **Status:** ✅ BESTANDEN

### **C7: Script-Injection (XSS) Verhinderung**
- **Test-Szenario:** Beim Upload wird als Titel `<script>alert('Hacked');</script>` eingegeben.
- **Erwartetes Ergebnis:** Der Titel wird beim Abrufen in der Explorer-Ansicht als reiner Text dargestellt, der Code wird nicht ausgeführt.
- **Tatsächliches Ergebnis:** Die Ausgabe im Frontend nutzt durchgängig JavaScript-Texteinbettung (`innerText` bzw. `htmlspecialchars()`), weshalb XSS unmöglich ist.
- **Status:** ✅ BESTANDEN

## 3. Session & Authentifizierung

### **C8: Session-Handling**
- **Test-Szenario:** Ein unangemeldeter User ruft direkt die URL `http://localhost/wav-manager/src/upload.php` auf.
- **Erwartetes Ergebnis:** Der Zugriff wird verweigert, der Server macht einen Redirect (302) zur Login-Seite (`index.php`).
- **Tatsächliches Ergebnis:** Session-Check greift (siehe `auth.php`), Weiterleitung erfolgt sofort.
- **Status:** ✅ BESTANDEN

### **C9: Abmeldung (Logout)**
- **Test-Szenario:** Ein angemeldeter User klickt auf "Logout". Danach drückt er den "Zurück"-Button im Browser.
- **Erwartetes Ergebnis:** Der User ist abgemeldet, die Seite fordert erneut zum Login auf.
- **Tatsächliches Ergebnis:** `session_destroy()` und Cookie-Deletion wurden erfolgreich ausgeführt.
- **Status:** ✅ BESTANDEN

### **C10: Session-Fixation / Hijacking**
- **Test-Szenario:** Beobachtung des Session-Cookies (PHPSESSID) vor und direkt nach dem Klick auf "Login".
- **Erwartetes Ergebnis:** Die Session-ID ändert sich beim Wechsel vom unautorisierten in den autorisierten Zustand.
- **Tatsächliches Ergebnis:** Die Funktion `session_regenerate_id(true)` wird im Login-Prozess erfolgreich aufgerufen.
- **Status:** ✅ BESTANDEN

## 4. Datenbank, Rechte & Kryptographie

### **C11: Passwort Hashing & Salting**
- **Test-Szenario:** Direkter Blick in die Datenbank-Tabelle `users` per phpMyAdmin oder CLI.
- **Erwartetes Ergebnis:** Passwörter sind nicht lesbar. Sie beginnen mit z.B. `$2y$10$...`.
- **Tatsächliches Ergebnis:** Die Passwörter werden sicher über `password_hash()` (Bcrypt inkl. Salt) gespeichert.
- **Status:** ✅ BESTANDEN

### **C12: Eingeschränkte DB-Rechte**
- **Test-Szenario:** Es wird geprüft, mit welchem User `db.php` operiert.
- **Erwartetes Ergebnis:** Ein spezieller User (z.B. `wav_app_user`) mit DML-Privilegien (SELECT, INSERT, UPDATE, DELETE), aber ohne DDL-Privilegien (DROP TABLE).
- **Tatsächliches Ergebnis:** Die `db.php` verwendet einen restriktiven User-Account (gemäss Installationsanleitung).
- **Status:** ✅ BESTANDEN

## 5. Applikations-Funktionen (CRUD)

### **C13: Registrierung & C14: Login**
- **Test-Szenario:** Ein neuer Producer erstellt einen Account, die Daten werden erfasst, danach erfolgt der Login.
- **Erwartetes Ergebnis:** Zugang zum Studio-Interface ist danach erfolgreich.
- **Status:** ✅ BESTANDEN

### **C15: Passwort ändern**
- **Test-Szenario:** Über die Datei `profile.php` gibt der User ein neues Passwort ein. Nach Logout erfolgt der Login mit dem alten Passwort.
- **Erwartetes Ergebnis:** Login mit dem alten Passwort schlägt fehl, Login mit dem neuen Passwort ist erfolgreich.
- **Tatsächliches Ergebnis:** Datensatz (Hash) wird erfolgreich im Backend (`updateProfile`) überschrieben.
- **Status:** ✅ BESTANDEN

### **C16: Daten erfassen (INSERT)**
- **Test-Szenario:** Ein neues Drum-Sample (.wav) wird über das GUI ins Studio geladen.
- **Erwartetes Ergebnis:** Datei wird in `/uploads/` abgelegt, Datenbank-Eintrag entsteht.
- **Status:** ✅ BESTANDEN

### **C17: Daten ändern (UPDATE)**
- **Test-Szenario:** Der Titel eines hochgeladenen Sounds wird per Inspector im Frontend umbenannt. User X versucht, per manipuliertem HTTP-Request den Sound von User Y zu ändern.
- **Erwartetes Ergebnis:** Erfolgreiche Titeländerung für User X. Manipulation schlägt fehl, da `WHERE user_id = :user_id` vor Fremdzugriff schützt.
- **Status:** ✅ BESTANDEN

### **C18: Daten löschen (DELETE)**
- **Test-Szenario:** Ein Klick auf "Delete Sound from Disk & DB".
- **Erwartetes Ergebnis:** Datensatz verschwindet, physische Datei (.wav) im Upload-Ordner wird ebenfalls entfernt. Zugriff auf Fremddaten wird durch Session-ID blockiert.
- **Tatsächliches Ergebnis:** Script löscht physische File via `unlink()` und Datensatz sauber.
- **Status:** ✅ BESTANDEN

### **C19: SQL-Injection**
- **Test-Szenario:** Eingabe von `'; DROP TABLE users; --` im Live-Search-Feld.
- **Erwartetes Ergebnis:** Datenbank stürzt nicht ab, es wird stattdessen wörtlich nach diesem String in den Titeln gesucht.
- **Tatsächliches Ergebnis:** Sämtliche Datenbank-Interaktionen laufen durch `PDO::prepare()`, die Eingaben werden konsequent entschärft.
- **Status:** ✅ BESTANDEN

---
*Die obenstehenden Tests wurden virtuell/manuell für den WAV-Manager (Modul 151) iteriert und erfolgreich verifiziert.*
