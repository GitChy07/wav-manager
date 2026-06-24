# Prüfungs-Guide: Code-Erklärungen & Kompetenzen Modul 151

Dieses Dokument ist dein Spickzettel und Leitfaden für das Präsentieren des WAV-Managers. Es listet alle relevanten Kompetenzen aus dem Bewertungsraster auf, zeigt exakt auf, **wo** im Code sie gelöst sind, und erklärt dir Zeile für Zeile **was** passiert und **warum** es gebraucht wird. 

*(Frontend "Slop" wie CSS-Animationen und Layout-Divs wurden hier bewusst ignoriert, damit du dich zu 100% auf die Modul-Ziele konzentrieren kannst).*

---

## 1. Handlungsziel 2: Sicherheit, Session-Handling & Validierung

### A1: Unterschied POST vs GET
* **Wissen für Prüfung:** 
  * `GET` hängt Formulardaten offen an die URL an (z.B. `?search=kick`). Wird für Datenabrufe (Suche, Filtern) genutzt.
  * `POST` versteckt die Daten im HTTP-Body. Wird zwingend für das Senden von sensiblen Daten (Login-Passwörter) oder das Hochladen von großen Dateien (WAV-Files) genutzt. Unser System nutzt `POST` in `upload.php` und `index.php` (Login).

### C5 & A3: Clientseitige Validierung
* **Wo im Code?** `upload-form.html` (Zeile 17)
  ```html
  <input type="file" id="wav_file" name="wav_file" accept=".wav" required>
  ```
* **Was macht das?**
  * `required`: Zwingt den Webbrowser, das Absenden zu blockieren, wenn der User keine Datei ausgewählt hat.
  * `accept=".wav"`: Beschränkt den Datei-Auswahldialog des Betriebssystems auf WAV-Dateien.
* **Warum wird das gebraucht?** Es entlastet den Server massiv und verbessert die User Experience (UX), da offensichtlich ungültige Eingaben sofort im Browser abgelehnt werden, ohne dass ein langsamer Seiten-Reload nötig ist.

### C6 & A4: Serverseitige Validierung
* **Wo im Code?** `upload.php` (Zeile 18)
  ```php
  if (isset($_FILES['wav_file']) && $_FILES['wav_file']['error'] === UPLOAD_ERR_OK) { ... }
  ```
* **Was macht das?**
  * `isset()` prüft, ob im HTTP-Request überhaupt ein Datei-Feld mit dem Namen `wav_file` mitgeschickt wurde.
  * `UPLOAD_ERR_OK` prüft den Statuscode des PHP-Servers, ob die Datei beim Upload unbeschädigt im temporären Server-Verzeichnis angekommen ist.
* **Warum wird das gebraucht?** Clientseitige Validierung (HTML) kann von Hackern kinderleicht manipuliert oder umgangen werden (z.B. durch Tools wie Postman). Der Server **muss** immer als letzte Instanz prüfen, ob die Daten wirklich korrekt sind, bevor sie in der Datenbank landen.

### C7 & A5: Script-Injection (XSS) verhindern
* **Wo im Code?** `upload.php` (Zeile 143)
  ```php
  $relation_options_html .= '<option value="..."> ' . htmlspecialchars($row['title']) . '</option>';
  ```
* **Was macht das?** 
  * `htmlspecialchars()` nimmt Text aus der Datenbank (den ein böswilliger User geschrieben haben könnte) und entschärft ihn. 
  * Aus dem gefährlichen HTML/JS-Code `<script>alert('Hacked')</script>` macht es den harmlosen String `&lt;script&gt;alert('Hacked')&lt;/script&gt;`.
* **Warum wird das gebraucht?** Es verhindert **Cross-Site-Scripting (XSS)**. Ohne diese Funktion würde der Browser eines anderen Users den Schadcode als echten Programmcode interpretieren und ausführen.

### C8, A6, C9, C10: Session-Handling & Logout
* **Wo im Code?** `includes/auth.php` (Zeilen 10, 64, 81)
  ```php
  // C8: Session starten
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  // C10: Nach erfolgreichem Login:
  session_regenerate_id(true);
  $_SESSION['user_id'] = $row['id'];

  // C9: Beim Logout:
  session_destroy();
  ```
* **Was macht das und warum?**
  * `session_start()`: Verbindet den Client (Browser) über ein Cookie (`PHPSESSID`) mit einem unsichtbaren Speicherplatz (`$_SESSION`) auf dem Server. Da HTTP statuslos ist, wüsste der Server sonst nach jedem Klick nicht mehr, wer man ist.
  * `$_SESSION['user_id']`: Der sicherste Ort, um zu speichern, wer angemeldet ist. Hacker können Cookies fälschen, aber nicht die `$_SESSION` auf dem Server manipulieren.
  * **C10 (Session-Fixation verhindern):** `session_regenerate_id(true)` generiert direkt nach dem Login eine komplett neue Session-ID. Wenn ein Hacker dem User vorher eine präparierte ID untergeschoben hat (Fixation), wird diese durch die neue ID sofort nutzlos.
  * **C9 (Logout):** `session_destroy()` löscht die Server-Akte des Users restlos.

### C11 & A8-A10: Passwort-Hashing & Salting
* **Wo im Code?** `includes/auth.php` (Zeilen 30 und 57)
  ```php
  // Registrierung:
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  // Login:
  if (password_verify($password, $row['password_hash'])) { ... }
  ```
* **Was macht das und warum?**
  * `password_hash()`: Generiert aus dem Klartext-Passwort einen unumkehrbaren Bcrypt-Hash. Selbst wenn die Datenbank gestohlen wird, kennt niemand die Passwörter.
  * **SALT (A9):** Ein Salt ist eine zufällige Zeichenfolge, die vom Algorithmus automatisch an das Passwort gehängt wird, *bevor* gehasht wird. Das sorgt dafür, dass zwei User mit dem Passwort "123456" völlig unterschiedliche Hashes in der DB haben. Das macht sogenannte "Rainbow Table" Angriffe (vorgefertigte Hash-Listen) nutzlos.
  * `password_verify()`: Checkt beim Login mathematisch, ob die Eingabe zum Hash passt.

---

## 2. Handlungsziel 3: Datenbank, Berechtigungen & SQL-Injection

### C12, A13, A14: Eingeschränkte DB-Rechte (Least Privilege Prinzip)
* **Was macht das und warum?** 
  Wir verwenden für die Applikation nicht den DB-Admin `root`, sondern einen dedizierten User (`wav_app_user`). Dieser User hat exklusiv nur **DML-Rechte** (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) auf die WAV-Manager Tabellen.
  **Warum?** Wenn es einem Angreifer gelingt, das Backend zu kompromittieren, kann er maximal Datensätze verändern, aber er hat keine Rechte für **DDL-Befehle** (wie `DROP TABLE users` oder `DROP DATABASE`), was den Komplettverlust der Architektur verhindert.

### C19, B13-B16: Schutz vor SQL-Injection (Prepared Statements)
* **Wo im Code?** Überall, z.B. in `includes/delete.php` (Zeile 40)
  ```php
  $delete_stmt = $pdo->prepare("DELETE FROM songs WHERE id = :id AND user_id = :user_id");
  $delete_stmt->execute(['id' => $sound_id, 'user_id' => $user_id]);
  ```
* **Was macht das?**
  * Anstatt die Variablen einfach in den SQL-String zu kleben (`"DELETE FROM songs WHERE id = " . $sound_id`), wird der Befehl mit Platzhaltern (`:id`) **vorbereitet** (`prepare()`).
  * Die eigentlichen Werte werden erst beim `execute()` mitgegeben.
* **Warum wird das gebraucht?** Die Datenbank verarbeitet die Struktur (den Query) und die Rohdaten strikt getrennt. Wenn ein User als ID `1; DROP TABLE users` eingibt, wird das von der DB nicht als weiterer Befehl ausgeführt, sondern rein als String/Text behandelt. **SQL-Injection ist damit zu 100% ausgeschlossen.**

### C17 & C18: Daten anpassen/löschen (Gatekeeping / Owner-Prinzip)
* **Wo im Code?** Z.B. in `includes/update_sound.php` oder `delete.php`
  ```php
  $stmt = $pdo->prepare("UPDATE songs SET title = :title WHERE id = :id AND user_id = :user_id");
  $stmt->execute(['title' => $title, 'id' => $id, 'user_id' => $_SESSION['user_id']]);
  ```
* **Was macht das und warum?**
  * Es wird immer ein `AND user_id = :user_id` an die Bedingung gehängt. Der Wert für `:user_id` stammt **niemals** aus dem Formular (da Hacker Formulare manipulieren können), sondern immer aus der absolut sicheren Server-Session (`$_SESSION['user_id']`).
  * **Warum?** Wenn User "Max" versucht, über einen manipulierten POST-Request das Sample mit ID 5 von User "Tom" umzubenennen, sucht die Datenbank nach `id=5 AND user_id=[Max's ID]`. Da das Sample aber Tom's ID hat, findet das Script nichts. Die Datei bleibt vor Modifikationen durch Fremde unangetastet.

---
**Tipp für die Abnahme:** Wenn dich der Experte fragt, warum du keine direkte String-Verkettung bei SQL gemacht hast, nennst du sofort das Zauberwort **Prepared Statements** und verweist darauf, dass die Datenbank dadurch Syntax von Daten trennen kann. Das ist die Kern-Antwort, die er für eine 6.0 hören will!
