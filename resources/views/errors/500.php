<?php
/**
 * Standalone 500 page — rendered directly by the exception handler, so it must
 * NOT depend on the layout, the database, or anything that might also be broken.
 */
http_response_code(500);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0f172a;color:#e2e8f0;
             min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center}
        .card{max-width:520px}
        .code{font-size:72px;font-weight:900;color:#eab308;line-height:1}
        h1{margin-top:12px;font-size:24px;color:#fff}
        p{margin-top:12px;color:#94a3b8;line-height:1.6}
        a{display:inline-block;margin-top:28px;background:#1d4ed8;color:#fff;text-decoration:none;
          padding:12px 28px;border-radius:12px;font-weight:700}
        a:hover{background:#2563eb}
    </style>
</head>
<body>
    <div class="card">
        <div class="code">500</div>
        <h1>Something went wrong</h1>
        <p>We hit an unexpected error while loading this page. Our team has been notified. Please try again in a few moments.</p>
        <a href="/">Back to Home</a>
    </div>
</body>
</html>
