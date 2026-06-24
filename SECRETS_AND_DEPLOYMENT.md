# 🔒 Deployment & Security Guidelines (WAV-Manager)

> [!WARNING]  
> **WICHTIGER SICHERHEITSHINWEIS FÜR DEN PRODUKTIVBETRIEB:**  
> Dieses Dokument enthält Standard-Zugangsdaten für die lokale Entwicklung und das Testing.  
> **Vor dem Deployment auf einen Live-Server müssen zwingend alle Passwörter geändert und sämtliche Test-Daten gelöscht werden!**

---

## 1. Datenbank-Zugangsdaten (lokales Testing)

Die Web-Applikation nutzt aus Sicherheitsgründen (Kompetenz C12: Least Privilege) nicht den `root`-User der Datenbank. 

**Standard-Zugangsdaten für die lokale Entwicklung:**
- **Host:** `localhost`
- **Datenbank-Name:** `wav_manager`
- **Benutzername:** `wav_app_user`
- **Passwort:** `wav_secure_pass`

> [!IMPORTANT]
> Die Datei `src/config/db.php` enthält diese Zugangsdaten im Klartext. Sie wurde daher in die `.gitignore` aufgenommen und wird **nicht** mehr auf GitHub veröffentlicht. Im Repository befindet sich nur noch die Vorlage `db.example.php`.

### To-Do für Live-Betrieb:
1. Ändere das Passwort des MySQL-Users `wav_app_user` in der Datenbank.
2. Trage das neue Passwort in deiner lokalen (nicht versionierten) `db.php` ein.

---

## 2. Test-Benutzer (Applikation)

In der Entwicklungs-Datenbank existieren vorgefertigte Test-Benutzer, um die Funktionen (Gatekeeping, Datentrennung) sofort testen zu können.

| Benutzername | E-Mail | Passwort (Klartext) |
| :--- | :--- | :--- |
| `admin_prod` | admin@wav-manager.local | `123456` |
| `test_user_1` | test1@wav-manager.local | `123456` |
| `test_user_2` | test2@wav-manager.local | `123456` |

*(Hinweis: Die Passwörter liegen in der Datenbank ausschliesslich gehasht als Bcrypt-String vor - Kompetenz C11).*

### To-Do für Live-Betrieb:
1. Logge dich als Test-User ein und lösche über die Explorer-Ansicht sämtliche Test-Audiodateien (Dies löscht sie physisch vom Server und aus der Datenbank).
2. Lösche die Test-User direkt über phpMyAdmin aus der Tabelle `users` (oder ändere zwingend deren Passwörter in der Applikation unter "Profil").

---

## 3. Entfernung von Test-Daten (Cleanup)

Das Projekt enthält im Ordner `src/uploads/` möglicherweise noch Test-Audiodateien (`.wav`), die von den Test-Usern hochgeladen wurden.

> [!TIP]
> **Bereinigung:** Leere den Ordner `src/uploads/` (bis auf eventuelle Platzhalter/Gitkeep-Dateien) vollständig, bevor du das Projekt produktiv schaltest. So stellst du sicher, dass keine urheberrechtlich geschützten oder unnötigen Dummy-Dateien mit dem Code ausgeliefert werden.
