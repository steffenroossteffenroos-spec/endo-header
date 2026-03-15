<?php
    // Fehler-Management: Unterdrückung im Frontend zur Vermeidung von Information Leakage
    ini_set('display_errors', 0);
    error_reporting(E_ALL);

    // Security-Layer: Authentifizierung über serverseitige Umgebungsvariablen (Secrets) um Api-Guthaben zu schützen
    $password = getenv('password');
    if (($_GET['pw'] ?? '') !== $password) die("Zutritt verweigert.");

    // API-Konfiguration über Replit-Secrets
    // alternativ: $apiKey = "YOUR_API_KEY";
    $apiKey = getenv('ApiKey');

    const IMG_ASPECT_RATIO = "1:1";

    const IMG_TEMPERATURE = 0.4;
    
    const TEXT_MODEL = "gemini-2.5-pro";
    
    const CI_PROMPT = "Style: Authentic, raw photojournalism and candid snapshot. \n" .
        "Subject Basis: Adaptive to the title. The image can show genuine people OR minimalist medical objects (pills, kits, devices, microscopes) OR clean microscopic views, depending on what fits best. \n" .
        "Quality: Photorealistic textures everywhere. NO plastic look, NO glossy 3D rendering style. If people are present: highly detailed natural eyes with realistic catchlights, slight facial asymmetry, \n" . 
        "visible skin pores, fine wrinkles, raw documentary feel, NO airbrushed makeup, sharp textile folds, genuine expressions matching the topic. \n" .
        "Colors & Light: Dominant soft blush pink (#FDF2F5), warm beige, and white as accents. Natural daylight, very bright, no harsh shadows. \n" .
        "Constraints: ABSOLUTELY NO TEXT, no labels, no signage. No red ribbons, no clinical gore, no internal anatomy.";

    const CI_RULES = "No text in image, no metaphors, no gore. return ONE candid snapshot description. Output ONLY the English scene description.";

    const SYSTEMRULE = "IMPORTANT: The generated images must never be interpreted as medical advice, diagnosis, or treatment recommendations. Do not show real medication brands or specific dosages.";

    const TASK = "Generate optimized prompt according to the topic. Image will be used as header for a news article. The image should be a single static scene, not a collage. The image should show real life documentary style, not a 3D rendering.";

    // --- AJAX ENDPUNKT ---
    if (isset($_GET['action']) && $_GET['action'] === 'generate') {
        header('Content-Type: application/json');

        $title = $_GET['title'] ?? '';
        $model = $_GET['model'] ?? 'gemini-2.5-flash'; // Bildmodell-Bezeichnung je nach API-Tier
        
        // 1. Schritt: Text-LLM entwirft den Prompt für einen Titel
        $aspectRatio = "1:1";
        
        $textUrl = "https://generativelanguage.googleapis.com/v1beta/models/". TEXT_MODEL .":generateContent?key={$apiKey}";

        $textPrompt = "Task: " . TASK ." Topic: '{$title}'. Rules: ". CI_RULES . " . CorporateIdenity-Rules: " . CI_PROMPT;
        
        // Api Call Text-Modell
        $ch1 = curl_init($textUrl);
        curl_setopt_array($ch1, [
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(["contents" => [["parts" => [["text" => $textPrompt]]]]])
        ]);

        $rawResponse = curl_exec($ch1);
        curl_close($ch1);
        
        $textResponse = json_decode($rawResponse, true);

        // Prompt für Bildmodell
        $final_prompt = $textResponse['candidates'][0]['content']['parts'][0]['text'] ?? $title;

        // Debugging
        // error_log("Final Prompt: \n" . $final_prompt);

        // 2. Schritt: Bild generieren
        $imgUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            "contents" => [["parts" => [["text" => $final_prompt]]]],
            "systemInstruction" => [
                "parts" => [["text" => SYSTEMRULE]]
            ],
            "generationConfig" => [
                "response_modalities" => ["IMAGE"],
                "temperature" => IMG_TEMPERATURE,
                "imageConfig" => ["aspectRatio" => IMG_ASPECT_RATIO]
            ]
        ];

        // Api Call Bildmodell                
        $ch = curl_init($imgUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
        exit;
    }
?>
            
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>EndoHeader - Titelbild Generator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div id="overlay" onclick="this.style.display='none'">
    <img src="" alt="Full Size">
</div>

<div class="container">
    <div class="header">
        <h1>Endo-Header</h1>
        <h2>Erstelle mit einem Klick KI-generierte Titelbilder im Endo-Design.</h2>
    </div>
    <div id="setup-view" class="setup-area">
        <div class="tabs">
            <button class="tab-btn active" onclick="loadTitles('news', this)">News</button>
            <button class="tab-btn" onclick="loadTitles('wissen', this)">Wissen</button>
            <button class="tab-btn" onclick="loadTitles('forschung', this)">Forschung</button>
        </div>
        <textarea id="titles-input" wrap="off"></textarea>
        
        <div style="display: flex; gap: 15px; align-items: center;margin-bottom:25px">
            <select id="model-select" >
                <option value="gemini-2.5-flash-image">Gemini 2.5 Flash Image</option>
                <option disabled value="gemini-3.1-flash-image-preview">Gemini 3.1 Flash Image Preview</option>
            </select>
        </div>
        <button class="btn" onclick="startBatch()" >Bilder generieren... (ca. 10 Sekunden pro Bild)</button>
    </div>
    
    <div id="batch-view">
        <div class="progress-bg"><div id="progress-bar"></div></div>
        <p id="progress-text" style="text-align: center; margin-bottom: 30px; color: green; font-weight: bold;"></p>
        <div class="grid" id="asset-grid"></div>
    </div>
    <button id="reset-btn" class="btn" style="display:none;" onclick="location.reload()">Neu beginnen</button>           
</div>

<script>
    // Demo-Titellisten für die verschiedenen Rubriken
    // (Live-Titel könnten auch aus dem Web, einer Datenbank oder einer API kommen)
    const lists = {
        news: [
            "Yselty® – Neu zugelassener Wirkstoff bei Endometriose",
            "Neues in der Endometriose-Behandlung: Relugolix",
            "Speicheltest für Endometriose: Kritische Stimmen",
            "Endometriose-Awareness im März",
            "Endo March: Was steht an? Wie kann ich aktiv werden?",
            "Langes Warten auf den Freischaltcode",
            "Juristische Waagschalen und Hammer",
            "Digitalisierung im Gesundheitswesen – DigiG",
            "Endo Health GmbH auf dem 15. Endometriose-Kongress",
            "Die Rolle von Fusobakterien"
        ].join('\n'),

        wissen: [
            "Früherkennung bei Endometriose",
            "Umweltfaktoren, Genetik und ihre Rolle",
            "Autoimmunerkrankungen und Endometriose",
            "Endometriose mit KI früher erkennen",
            "Zellstudien machen Hoffnung",
            "Biomarker & Endometriose",
            "Endometriose: Fakten statt Mythen",
            "Grundlagenforschung",
            "Wahlprogramme im Endo-Check",
            "Yoga im Sitzen"
        ].join('\n'),

        forschung: [
            "Endometriose und Fatigue",
            "Woher kommt Endometriose?",
            "Fusobakterien bei der Entstehung",
            "Tumormarker in der Diagnostik?",
            "Natürlich schwanger werden",
            "PMDS vs PMS Unterschied",
            "Grünteekomplex Schmerzen",
            "Endometriose, Reizdarm und Essstörungen",
            "Prämenstruelles Syndrom (PMS)",
            "Vitamin C & Vitamin E Therapie"
        ].join('\n')
    };

    // Initiale Belegung des Input-Bereichs
    document.getElementById('titles-input').value = lists.news;
    
    function loadTitles(cat, btn) {
        document.getElementById('titles-input').value = lists[cat];
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    
        
    function openOverlay(src) {
        const overlay = document.getElementById('overlay');
        overlay.querySelector('img').src = src;
        overlay.style.display = 'flex';
    }
    
    /**
     * Sequentielle Batch-Verarbeitung via Async/Await.
     * Verhindert API-Rate-Limit-Konflikte und sorgt für eine flüssige UI-Rückmeldung.
     */
    async function startBatch() {
        const input = document.getElementById('titles-input').value;
        const model = document.getElementById('model-select').value;
        const titles = input.split('\n').filter(t => t.trim() !== '');
        
        document.getElementById('setup-view').style.display = 'none';
        document.getElementById('batch-view').style.display = 'block';
        
        const grid = document.getElementById('asset-grid');
        grid.innerHTML = '';
    
        // Skeleton-UI Erstellung für die erwarteten Assets
        titles.forEach((title, i) => {
            grid.innerHTML += `
                <div class="card" id="card-${i}">
                    <strong>#${i+1}: ${title}</strong>
                    <div class="loader-box">
                        <div class="spinner"></div>
                        <span style="color: #64748b;">Warte auf API...</span>
                    </div>
                    <img id="img-${i}" src="" onclick="openOverlay(this.src)">
                    <a id="dl-${i}" class="download-btn">Download</a>
                </div>
            `;
        });
    
        for (let i = 0; i < titles.length; i++) {
            const card = document.getElementById(`card-${i}`);
            card.classList.add('active');
            document.getElementById('progress-text').innerText = `Generiere ${i+1} von ${titles.length}...`;
    
            try {
                const pw = new URLSearchParams(window.location.search).get('pw');
                const response = await fetch(`?action=generate&pw=${pw}&model=${model}&title=${encodeURIComponent(titles[i])}`);
                
                const result = await response.json();
                const base64 = result.candidates?.[0]?.content?.parts?.[0]?.inlineData?.data;
    
                if (base64) {
                    const mime = result.candidates[0].content.parts[0].inlineData.mimeType;
                    const dataUrl = `data:${mime};base64,${base64}`;
                    const img = document.getElementById(`img-${i}`);
                    img.src = dataUrl;
                    
                    // Download Link 
                    const dl = document.getElementById(`dl-${i}`);
                    dl.href = dataUrl;
                    dl.download = `Endo_Asset_${i+1}.png`;
    
                    card.classList.remove('active');
                    card.classList.add('done');
                } else {
                    const err = result.error?.message || "Safety Block";
                    card.innerHTML += `<p style="color:#ef4444; font-size:12px; margin-top:10px;">⚠️ ${err}</p>`;
                    card.classList.remove('active');
                    card.classList.add('done');
                }
            } catch (e) { console.error(e); }
    
            const percent = ((i + 1) / titles.length) * 100;
            document.getElementById('progress-bar').style.width = percent + '%';
        }
        document.getElementById('progress-text').innerText = "Abgeschlossen!";
        document.getElementById('reset-btn').style.display = 'block';
        
    }
</script>
</body>
</html>