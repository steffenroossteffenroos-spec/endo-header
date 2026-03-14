<?php
echo "Hallo Welt - der Server läuft!";
exit;
    // Error Reporting an 
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // simpler passwort schutz gegen bots.
    $password = getenv('password');
    if (($_GET['pw'] ?? '') !== $password) die("Zutritt verweigert.");

    // apiKey für Google Gemini aus Umgebungsvariable laden
    $apiKey = getenv('ApiKey');

    // --- AJAX ENDPUNKT ---
    if (isset($_GET['action']) && $_GET['action'] === 'generate') {
        header('Content-Type: application/json');
        $title = $_GET['title'] ?? '';
        $model = $_GET['model'] ?? 'gemini-2.5-flash-image';
        
        // CI Master Prompt
        $ci_prompt = "Style: Bright, airy documentary health photography and photorealistic still life. \n" .
         "Subject Basis: Adaptive to the title. The image can show genuine people OR minimalist medical objects (pills, kits, devices) OR clean microscopic/conceptual views, depending on what fits best. \n" .
         "Quality: Photorealistic textures everywhere. NO plastic look, NO glossy 3D rendering style. If people are present: real skin texture, genuine expressions matching the topic (skeptical vs positive). \n" .
         "Colors & Light: Dominant soft blush pink (#FDF2F5), warm beige, and white. Natural daylight, very bright, no harsh shadows. \n" .
         "Constraints: ABSOLUTELY NO TEXT, no labels, no signage. No red ribbons, no clinical gore, no internal anatomy.";
    
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $payload = [
            "contents" => [["parts" => [["text" => $ci_prompt . " Subject: " . $title]]]],
            "generationConfig" => ["response_modalities" => ["IMAGE"]]
        ];
    
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    
        sleep(2); // Rate Limit Schutz
        echo $response;
        exit;
    }
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Endo-Header - Titelbild Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div id="overlay" onclick="this.style.display='none'">
    <img src="" alt="Full Size">
</div>

<div class="container">
    <h1>Endo-Header - Titelbild Generator</h1>

    <div id="setup-view" class="setup-area">
        <div class="tabs">
            <button class="tab-btn active" onclick="loadTitles('news', this)">News</button>
            <button class="tab-btn" onclick="loadTitles('wissen', this)">Wissen</button>
            <button class="tab-btn" onclick="loadTitles('forschung', this)">Forschung</button>
        </div>
        
        <textarea id="titles-input"></textarea>
        
        <div style="display: flex; gap: 15px; align-items: center;">
            <select id="model-select" style="padding: 12px; border-radius: 50px; border: 2px solid var(--endo-border); font-weight: 700; color: var(--endo-navy);">
                <option value="gemini-2.5-flash-image">Gemini 2.5 Flash Image</option>
                <option disabled value="gemini-3.1-flash-image-preview">Gemini 3.1 Flash Image Preview</option>
            </select>

            
            <button class="btn" onclick="startBatch()">Bilder generieren...</button>
        </div>
    </div>

    <div id="batch-view">
        <div class="progress-bg"><div id="progress-bar"></div></div>
        <p id="progress-text" style="text-align: center; margin-bottom: 30px; color:#color: green; font-weight: bold;"></p>
        <div class="grid" id="asset-grid"></div>
    </div>
</div>

<script>
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

async function startBatch() {
    const input = document.getElementById('titles-input').value;
    const model = document.getElementById('model-select').value;
    const titles = input.split('\n').filter(t => t.trim() !== '');
    
    document.getElementById('setup-view').style.display = 'none';
    document.getElementById('batch-view').style.display = 'block';
    
    const grid = document.getElementById('asset-grid');
    grid.innerHTML = '';

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
            //const response = await fetch(`?action=generate&model=${model}&title=${encodeURIComponent(titles[i])}`);
            const pw = new URLSearchParams(window.location.search).get('pw');
            const response = await fetch(`?action=generate&pw=${pw}&model=${model}&title=${encodeURIComponent(titles[i])}`);
            
            const result = await response.json();
            const base64 = result.candidates?.[0]?.content?.parts?.[0]?.inlineData?.data;

            if (base64) {
                const mime = result.candidates[0].content.parts[0].inlineData.mimeType;
                const dataUrl = `data:${mime};base64,${base64}`;
                const img = document.getElementById(`img-${i}`);
                img.src = dataUrl;
                
                // Download Link schärfen
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
}
</script>
</body>
</html>