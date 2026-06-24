# 🔒 Deployment & Security Guidelines (WAV-Manager)

> [!WARNING]  
> **WICHTIGER SICHERHEITSHINWEIS FÜR DEN PRODUKTIVBETRIEB:**  
> Dieses Dokument enthält Standard-Zugangsdaten für die lokale Entwicklung und das Testing.  
> **Vor dem Deployment auf einen Live-Server müssen zwingend alle Passwörter geändert und sämtliche Test-Daten gelöscht werden!**

---

## 1. Datenbank-Zugangsdaten (lokales Testing)

Die Web-Applikation nutzt aus Sicherheitsgründen (Least Privilege Principle) nicht den `root`-User der Datenbank. 

**Standard-Zugangsdaten für die lokale Entwicklung:**
- **Host:** `localhost`
- **Datenbank-Name:** `wav_manager`
- **Benutzername:** `wav_app_user`
- **Passwort:** `wav_secure_pass`

> [!IMPORTANT]
> Die Datei `src/config/db.php` enthält diese Zugangsdaten im Klartext. Sie wurde daher in die `.gitignore` aufgenommen und wird **nicht** veröffentlicht. Im Repository befindet sich als Vorlage stattdessen die Datei `db.example.php`.

### To-Do für Live-Betrieb:
1. Ändere das Passwort des MySQL-Users `wav_app_user` in der Datenbank.
2. Trage das neue Passwort in deiner lokalen (nicht versionierten) `db.php` ein.

---

## 2. Test-Benutzer (Applikation)

In der Entwicklungs-Datenbank existiert ein vorgefertigter Test-Benutzer, um die Applikation nach der Installation sofort testen zu können.

| Benutzername | E-Mail | Passwort (Klartext) |
| :--- | :--- | :--- |
| `producer1` | producer1@wav-manager.local | `123456` |

*(Hinweis: Die Passwörter liegen in der Datenbank ausschliesslich gehasht als Bcrypt-String vor).*

### To-Do für Live-Betrieb:
1. Logge dich als Test-User ein und lösche über die Explorer-Ansicht sämtliche Test-Audiodateien (Dies löscht sie physisch vom Server und aus der Datenbank).
2. Lösche den Test-User direkt über phpMyAdmin aus der Tabelle `users` (oder ändere zwingend sein Passwort in der Applikation).

---

## 3. Entfernung von Test-Daten (Cleanup)

Das Projekt enthält im Ordner `uploads/` einige Test-Audiodateien (`.wav`), die von den Test-Usern für Demo-Zwecke hochgeladen wurden.

> [!TIP]
> **Bereinigung:** Leere den Ordner `uploads/` (bis auf die `.gitkeep`-Datei) vollständig, bevor du das Projekt produktiv schaltest. So stellst du sicher, dass keine fremden Audio-Dateien mit dem Code ausgeliefert werden.
