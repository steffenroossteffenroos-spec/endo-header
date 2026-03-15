EndoHeader – Automatisierter KI-Bild-Generator (MVP)
Dieses Projekt ist ein funktionales Minimum Viable Product (MVP) zur Skalierung der Content-Erstellung. Es automatisiert die Generierung von markenkonsistenten Header-Bildern aus redaktionellen Titeln. Die Anwendung kombiniert Text-Analyse mit generativer Bild-KI, um den manuellen Design-Prozess zu ersetzen.

System-Architektur & Logik
Die App nutzt eine Multi-Stage-Pipeline, um die Schwächen einfacher Text-zu-Bild-Prompts (wie mangelnder Kontext oder Sprachbarrieren) zu umgehen:

1. Semantische Klassifizierung (Regex-Engine)
Bevor die KI aufgerufen wird, analysiert das PHP-Backend den Eingabetext mittels Regular Expressions. Es erkennt vordefinierte Themencluster (z. B. Forschung, Politik, Akutschmerz) und weist dem Request eine spezifische atmosphärische Leitplanke zu. Dies stellt sicher, dass ein Artikel über Gesetze eine seriöse Optik erhält, während ein Text über Yoga einen entspannten Vibe bekommt.

2. Szenen-Synthese (LLM-Intermediary)
Anstatt den deutschen Titel direkt an die Bild-KI zu senden, wird Gemini 1.5 Flash als "Regisseur" zwischengeschaltet.

Input: Deutscher Fachtitel.

Output: Eine präzise, englische Beschreibung einer fotorealistischen Szene.

Ziel: Die Bild-KI liefert bessere Ergebnisse, wenn sie eine konkrete Szenerie (z. B. "eine Person hält eine Tasse Tee in weichem Tageslicht") statt abstrakter Titel verarbeitet.

3. Bild-Generierung & CI-Enforcement
Der finale Request an Gemini 2.5 Flash Image (Imagen 3 Technologie) kombiniert die generierte Szene mit einem statischen Base-CI-Prompt. Dieser erzwingt:

Die Farbklima-Vorgaben (#FDF2F5, Beige, Weiß).

Technische Parameter wie Brennweite (50mm) und Tiefenschärfe (Bokeh).

Die Einhaltung des dokumentarischen Fotostils.

Technischer Stack
Backend: PHP 8.4. Die Wahl fiel auf ein framework-loses Backend, um maximale Performance und geringstmögliche Latenz beim API-Proxying zu erreichen.

Infrastruktur: Gehostet auf Replit. Die Umgebung nutzt Port 8080 und verwaltet sensible API-Daten über verschlüsselte Environment Variables (Secrets).

Frontend: Vanilla JavaScript. Die Batch-Verarbeitung erfolgt asynchron (AJAX), damit die Benutzeroberfläche während der 10-20 sekündigen Rechenzeit pro Bild reaktiv bleibt.

Mobile Ready: Die Oberfläche nutzt ein fluides CSS-Grid und Viewport-Optimierungen, um die Steuerung der Content-Pipeline direkt vom Smartphone aus zu ermöglichen.

Brand Safety & Compliance
Im medizinischen Kontext ist "Brand Safety" kritisch. Das System verfügt über mehrere Sicherheitslayer:

System Instructions: Über die API-Parameter wird die KI strikt angewiesen, keine medizinischen Diagnosen, keine Logos und keinen Text in Bildern darzustellen.

Safety Filter: Die Anwendung fängt "Safety Blocks" der Google-API ab und gibt diese als kontrollierte Fehlermeldungen aus, statt den Batch-Prozess zu unterbrechen.

Stateless Processing: Es werden keine Bilder permanent auf dem Server gespeichert (Base64-Streaming). Dies minimiert den Speicherbedarf und erhöht den Datenschutz.

Deployment & Betrieb
Secrets: Hinterlegen von ApiKey (Google AI Studio) und password (App-Zugang) in den Replit-Einstellungen.

Auth: Der Zugriff erfolgt über einen passwortgeschützten URL-Parameter (?pw=...), der serverseitig validiert wird.

Batch-Modus: Nutzer können aus Presets wählen oder eigene Listen einfügen. Ein sleep(2) im Backend sorgt für die Einhaltung von API-Rate-Limits.