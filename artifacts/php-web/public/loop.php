<?php
header('Content-Type: text/html; charset=utf-8');

if (ob_get_level()) ob_end_clean();
header('X-Accel-Buffering: no');

$apiKey =getenv('ApiKey');
$model_id = $_GET['model_id'] ?? "gemini-2.5-flash-image";

$titlesNews = [
    "Yselty® – Neu zugelassener Wirkstoff bei Endometriose",
    "Neues in der Endometriose-Behandlung: Die Relugolix-Kombinationstherapie",
    "Speicheltest für Endometriose: Das sagen die kritischen Stimmen",
    "Endometriose-Awareness im März",
    "Endo March: Was steht an? Wie kann ich aktiv werden?",
    "Langes Warten auf den Freischaltcode: Das soll sich jetzt ändern",
    "Juristische Waagschalen und Hammer.",
    "Mehr Digitalisierung im Gesundheitswesen – Das neue DigiG bringt viel Positives für Patient:innen",
    "Endo Health GmbH auf dem 15. Endometriose-Kongress",
    "Die Rolle von Fusobakterien bei der Entstehung von Endometriose"
];
$titlesWissen = [
    "Früherkennung bei Endometriose",
    "Umweltfaktoren, Genetik und ihre Rolle bei Endometriose",
    "Autoimmunerkrankungen und Endometriose",
    "Endometriose mit künstlicher Intelligenz früher erkennen",
    "Endometriose besser verstehen: Neue Zellstudien machen Hoffnung",
    "Biomarker & Endometriose",
    "Endometriose: Fakten statt Mythen",
    "Grundlagenforschung",
    "Wahlprogramme im Endo-Check – Bundestagswahl 2025",
    "Yoga im Sitzen"
];

$titlesForschung = [
    "Endometriose und Fatigue: Mehr als „Ich bin auch manchmal müde“",
    "Woher kommt Endometriose?",
    "Die Rolle von Fusobakterien bei der Entstehung von Endometriose",
    "Tumormarker in der Endometriose-Diagnostik?",
    "Natürlich schwanger werden mit Endometriose und Adenomyose – Was du selbst tun kannst",
    "Prämenstruelle Dysphorische Störung (PMDS) und der Unterschied zum Prämenstruellen Syndrom (PMS)",
    "Der Einfluss von Grünteekomplex oder -Extrakt auf Schmerzen und Schleimhautabbau",
    "Endometriose, Reizdarm und Essstörungen – Stimme aus der Praxis",
    "Was in deinem Körper eine Woche vor der Periode geschieht – Das Prämenstruelle Syndrom (PMS)",
    "Vitamin C & Vitamin E in der Therapie von Endometriose"
];

$titles = $titlesNews;
    

$total = count($titles);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Batch Generator</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --primary: #38bdf8; }
        body { font-family: sans-serif; background: var(--bg); color: #f8fafc; padding: 40px; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; }
        .btn { background: var(--primary); color: #0f172a; padding: 15px 40px; border: none; border-radius: 8px; font-weight: bold; text-decoration: none; display: inline-block; cursor: pointer; font-size: 16px; }

        select { background: #334155; color: #f8fafc; border: 1px solid #475569; padding: 12px; border-radius: 8px; font-size: 16px; width: 100%; max-width: 400px; margin-bottom: 20px; outline: none; }
        select option:disabled { color: #64748b; background: #1e293b; }

        .progress-wrapper { display: none; margin: 30px 0; }
        .progress-bg { background: #334155; width: 100%; height: 24px; border-radius: 12px; overflow: hidden; }
        .progress-bar { background: var(--primary); width: 0%; height: 100%; transition: width 0.5s ease-in-out; }
        .progress-text { text-align: center; margin-top: 10px; font-weight: bold; color: #94a3b8; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: var(--card); border-radius: 12px; padding: 20px; border: 1px solid #334155; display: flex; flex-direction: column; }
        .card strong { color: var(--primary); font-size: 1.1em; display: block; margin-bottom: 5px; }
        .prompt-text { font-size: 0.85em; color: #94a3b8; margin-bottom: 15px; font-style: italic; background: #0f172a; padding: 8px; border-radius: 6px; }
        .card img { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); cursor: zoom-in; transition: transform 0.2s; }
        .card img:hover { transform: scale(1.02); }

        .download-btn { background: #10b981; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-size: 0.9em; margin-top: 15px; display: inline-block; text-align: center; transition: background 0.2s; }
        .download-btn:hover { background: #059669; }

        /* Overlay Styles */
        .overlay { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); justify-content: center; align-items: center; }
        .overlay img { max-width: 90vw; max-height: 90vh; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .close-btn { position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; cursor: pointer; user-select: none; }
    </style>
</head>
<body>

<div class="container">
    <h1 style="text-align: center;">Endo App Banner Generator</h1>

    <?php if (!isset($_GET['start'])): ?>
        <div style="text-align: center; margin-top: 50px;">
            <form method="GET" action="">
                <input type="hidden" name="start" value="1">

                <label for="model_id" style="display: block; margin-bottom: 10px; font-weight: bold;">Modell auswählen:</label>
                <select name="model_id" id="model_id">
                    <option value="gemini-2.5-flash-image">Gemini 2.5 Flash Image (Schnell & Stabil)</option>
                    <option value="gemini-3.1-flash-image-preview">Gemini 3.1 Flash Image (Neueste Version)</option>
                    <option value="gemini-3-pro-image-preview" disabled>Gemini 3 Pro Image (Nano Banana Pro - Teuer)</option>
                </select>

                <br>
                <button type="submit" class="btn">Prozess starten</button>
            </form>
        </div>
    <?php else: ?>

        <div class="progress-wrapper" id="p-wrapper">
            <div class="progress-bg">
                <div class="progress-bar" id="p-bar"></div>
            </div>
            <div class="progress-text" id="p-text">0 von <?php echo $total; ?> Bildern generiert (0%)</div>
        </div>

        <script>document.getElementById('p-wrapper').style.display = 'block';</script>

        <div class="grid">
        <?php
        echo str_repeat(' ', 1024); 
        flush();

        foreach ($titles as $index => $title) {
            $prompt = "Professional artistic image of: " . $title;

            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:generateContent?key=" . $apiKey;

            $payload = [
                "contents" => [["parts" => [["text" => $prompt]]]],
                "generationConfig" => ["response_modalities" => ["IMAGE"]]
            ];

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload)
            ]);

            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            $imageBase64 = $result['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;
            $mime = $result['candidates'][0]['content']['parts'][0]['inlineData']['mimeType'] ?? 'image/png';

            $imgSrc = $imageBase64 ? "data:{$mime};base64,{$imageBase64}" : '';
            $filename = "bild_" . ($index + 1) . ".png";

            ?>
            <div class="card">
                <strong>#<?php echo ($index + 1); ?>: <?php echo htmlspecialchars($title); ?></strong>
                <div class="prompt-text">Prompt: <?php echo htmlspecialchars($prompt); ?></div>

                <?php if ($imageBase64): ?>
                    <img src="<?php echo $imgSrc; ?>" alt="Generiertes Bild" onclick="openOverlay('<?php echo $imgSrc; ?>')">
                    <a href="<?php echo $imgSrc; ?>" download="<?php echo $filename; ?>" class="download-btn">⬇ Herunterladen</a>
                <?php else: ?>
                    <p style="color: #ef4444;">Fehler <?php echo $status; ?>:<br><small><?php echo htmlspecialchars($result['error']['message'] ?? ''); ?></small></p>
                <?php endif; ?>
            </div>
            <?php

            $current = $index + 1;
            $percent = round(($current / $total) * 100);

            ?>
            <script>
                document.getElementById("p-bar").style.width = "<?php echo $percent; ?>%";
                document.getElementById("p-text").innerText = "<?php echo $current; ?> von <?php echo $total; ?> Bildern generiert (<?php echo $percent; ?>%)";
            </script>
            <?php

            flush();

            if ($current < $total) {
                sleep(2); 
            }
        }
        ?>
        </div>

        <script>
            document.getElementById("p-text").innerText = "Prozess abgeschlossen!";
            document.getElementById("p-text").style.color = "#4ade80";
        </script>

        <div style="text-align: center; margin-top: 40px;">
            <a href="?" class="btn" style="background: #334155; color: white;">Neuen Durchlauf starten</a>
        </div>

    <?php endif; ?>
</div>

<div id="imageOverlay" class="overlay" onclick="closeOverlay()">
    <span class="close-btn">&times;</span>
    <img id="overlayImage" src="" alt="Vollbild">
</div>

<script>
    function openOverlay(src) {
        document.getElementById('overlayImage').src = src;
        document.getElementById('imageOverlay').style.display = 'flex';
    }

    function closeOverlay() {
        document.getElementById('imageOverlay').style.display = 'none';
        document.getElementById('overlayImage').src = '';
    }

    // Schließen mit Escape-Taste
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeOverlay();
        }
    });
</script>

</body>
</html>