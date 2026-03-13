<?php
$name = htmlspecialchars($_GET['name'] ?? 'World');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hello <?= $name ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            max-width: 400px;
        }
        h1 { font-size: 2.5rem; margin-bottom: 0.5rem; color: #818cf8; }
        p { color: #94a3b8; margin-bottom: 1.5rem; }
        form { display: flex; gap: 0.5rem; }
        input {
            flex: 1;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border: 1px solid #334155;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 1rem;
            outline: none;
        }
        input:focus { border-color: #4f46e5; }
        button {
            padding: 0.6rem 1.2rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover { background: #4338ca; }
        a { display: block; margin-top: 1.5rem; color: #64748b; font-size: 0.875rem; text-decoration: none; }
        a:hover { color: #818cf8; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Hello, <?= $name ?>!</h1>
        <p>Try greeting someone else:</p>
        <form method="GET">
            <input type="text" name="name" placeholder="Enter a name..." value="<?= $name ?>">
            <button type="submit">Go</button>
        </form>
        <a href="/">← Back to home</a>
    </div>
</body>
</html>
