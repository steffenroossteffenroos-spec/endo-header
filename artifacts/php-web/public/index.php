<?php
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$phpVersion = phpversion();
$serverTime = date('Y-m-d H:i:s');
$memoryUsage = round(memory_get_usage(true) / 1024, 2);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Web Server</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo {
            background: #4f46e5;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        header h1 { font-size: 1.1rem; font-weight: 600; }
        header span { color: #94a3b8; font-size: 0.85rem; margin-left: auto; }
        main {
            flex: 1;
            padding: 2rem;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }
        .hero {
            text-align: center;
            padding: 3rem 0 2rem;
        }
        .hero h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p { color: #94a3b8; font-size: 1rem; }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 1.25rem;
        }
        .card-label { color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
        .card-value { font-size: 1.1rem; font-weight: 600; color: #e2e8f0; }
        .card-value.green { color: #4ade80; }
        .section {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .section h3 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #c7d2fe; }
        .route-list { list-style: none; }
        .route-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid #1e293b;
        }
        .route-list li:last-child { border-bottom: none; }
        .method {
            background: #312e81;
            color: #a5b4fc;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            letter-spacing: 0.05em;
        }
        .route-path { font-family: monospace; font-size: 0.9rem; color: #e2e8f0; }
        .route-desc { color: #64748b; font-size: 0.85rem; margin-left: auto; }
        a { color: #818cf8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        footer {
            text-align: center;
            color: #475569;
            font-size: 0.8rem;
            padding: 1.5rem;
            border-top: 1px solid #1e293b;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">PHP</div>
        <h1>PHP Web Server</h1>
        <span>Running on PHP <?= htmlspecialchars($phpVersion) ?></span>
    </header>
    <main>
        <div class="hero">
            <h2>Your PHP Server is Live</h2>
            <p>Edit files in <code>artifacts/php-web/public/</code> to build your app.</p>
        </div>
        <div class="cards">
            <div class="card">
                <div class="card-label">PHP Version</div>
                <div class="card-value"><?= htmlspecialchars($phpVersion) ?></div>
            </div>
            <div class="card">
                <div class="card-label">Server Time</div>
                <div class="card-value"><?= htmlspecialchars($serverTime) ?></div>
            </div>
            <div class="card">
                <div class="card-label">Memory Usage</div>
                <div class="card-value green"><?= $memoryUsage ?> KB</div>
            </div>
            <div class="card">
                <div class="card-label">Status</div>
                <div class="card-value green">Running</div>
            </div>
        </div>
        <div class="section">
            <h3>Available Routes</h3>
            <ul class="route-list">
                <li>
                    <span class="method">GET</span>
                    <span class="route-path"><a href="/">/</a></span>
                    <span class="route-desc">This page</span>
                </li>
                <li>
                    <span class="method">GET</span>
                    <span class="route-path"><a href="/info.php">/info.php</a></span>
                    <span class="route-desc">PHP info page</span>
                </li>
                <li>
                    <span class="method">GET</span>
                    <span class="route-path"><a href="/hello.php">/hello.php</a></span>
                    <span class="route-desc">Hello World example</span>
                </li>
            </ul>
        </div>
        <div class="section">
            <h3>Server Environment</h3>
            <ul class="route-list">
                <li>
                    <span class="route-path">Request URI</span>
                    <span class="route-desc"><?= htmlspecialchars($requestUri) ?></span>
                </li>
                <li>
                    <span class="route-path">Server Software</span>
                    <span class="route-desc"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in Server') ?></span>
                </li>
                <li>
                    <span class="route-path">Document Root</span>
                    <span class="route-desc"><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '') ?></span>
                </li>
            </ul>
        </div>
    </main>
    <footer>PHP <?= htmlspecialchars($phpVersion) ?> &mdash; Built-in Development Server</footer>
</body>
</html>
