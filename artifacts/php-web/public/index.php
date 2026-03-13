
<?php
/**
 * Gemini Nano Banana Pro - Bildgenerator
 * Charset: UTF-8
 */
//$apiKey = $_SERVER['EndoImage'];
$apiKey = getenv('ApiKeyEndoImage');


// Modell-Konfiguration (Nano Banana Pro)
$modelFlashLite = "gemini-2.0-flash-lite";
$modelFlash = "gemini-3.1-flash-image-preview";
$modelPro = "gemini-2.5-flash-image";

$model_id = $modelFlash;

// 1. Sicherheit: Fehleranzeige (im Live-Betrieb auf 0 setzen)
error_reporting(E_ALL);
ini_set('display_errors', 0);


// 2. Charset Header für den Browser
header('Content-Type: text/html; charset=utf-8');

// 3. DEIN FESTER SCHLÜSSEL
// Wichtig: In einer Produktionsumgebung besser über eine .env Datei laden!
define('GEMINI_API_KEY', $apiKey);

$generatedImageBase64 = null;
$errorMsg = null;
$mimeType = "image/png";

// 4. Logik bei Formular-Absendung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
    $userTitle = trim($_POST['title']);
    
    // API Endpoint
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:generateContent?key=" . GEMINI_API_KEY;

    // Payload für die Bildgenerierung
    $data = [
        "contents" => [
            ["parts" => [["text" => "Generate a professional, high-fidelity image based on this title: " . $userTitle]]]
        ],
        "generationConfig" => [
            "response_modalities" => ["IMAGE"] // Erzwingt Bild-Ausgabe
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpStatus === 200 && isset($result['candidates'][0]['content']['parts'])) {
        foreach ($result['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['inlineData'])) {
                $generatedImageBase64 = $part['inlineData']['data'];
                $mimeType = $part['inlineData']['mimeType'];
                break;
            }
        }
    } else {
        $errorMsg = "API-Fehler ({$httpStatus}): " . ($result['error']['message'] ?? "Unbekannter Fehler.");
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nano Banana Pro Generator</title>
    <style>
        :root { --primary: #1a73e8; --bg: #f8f9fa; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #333; display: flex; justify-content: center; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 600px; }
        h1 { color: var(--primary); font-size: 24px; margin-bottom: 20px; }
        input[type="text"] { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; margin-bottom: 15px; }
        button { background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; }
        button:hover { background: #1557b0; }
        .result-box { margin-top: 25px; text-align: center; }
        .result-box img { max-width: 100%; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #d93025; background: #fce8e6; padding: 15px; border-radius: 6px; margin-top: 15px; }
    </style>
</head>
<body>
    test: <?php echo getenv('password') ?>;
    
	<div class="container">
		<h1>Bild-Analyse & Generator</h1>
		<p>Gib einen Titel ein, um ein Bild mit <strong>Nano Banana Pro</strong> zu erstellen.</p>
		
		<form method="POST">
			<input type="text" name="title" placeholder="z.B. Ein neon-leuchtender Astronaut im Dschungel" required 
				   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
			<button type="submit">Bild jetzt generieren</button>
		</form>

		<?php if ($errorMsg): ?>
			<div class="error"><?php echo htmlspecialchars($errorMsg); ?></div>
		<?php endif; ?>

		<?php if ($generatedImageBase64): ?>
			<div class="result-box">
				<h3>Dein Ergebnis:</h3>
				<img src="data:<?php echo $mimeType; ?>;base64,<?php echo $generatedImageBase64; ?>" alt="Generiertes Bild">
				<p><small>Tipp: Rechtsklick zum Speichern</small></p>
			</div>
		<?php endif; ?>
	</div>
</body>
</html>