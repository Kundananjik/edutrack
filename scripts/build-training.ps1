$ErrorActionPreference = 'Stop'

$outDir = 'training'
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

$files = @{
    'about.php' = 'About'
    'help.php' = 'Help and FAQ'
    'privacy-policy.php' = 'Privacy Policy'
    'terms-of-service.php' = 'Terms of Service'
}

foreach ($entry in $files.GetEnumerator()) {
    $raw = Get-Content -Raw $entry.Key
    $noPhp = [regex]::Replace($raw, '(?s)<\?php.*?\?>', ' ')
    $noScript = [regex]::Replace($noPhp, '(?is)<(script|style)[^>]*>.*?</\1>', ' ')

    $withBreaks = $noScript -replace '(?i)<br\s*/?>', "`n"
    $withBreaks = $withBreaks -replace '(?i)</(p|h1|h2|h3|h4|h5|h6|li|div|section|article|header|footer|main|nav|ul|ol|table|tr|td|th|blockquote|pre)>', "`n"
    $withBreaks = $withBreaks -replace '(?i)<li[^>]*>', '- '

    $stripped = [regex]::Replace($withBreaks, '<[^>]+>', ' ')
    $decoded = [System.Net.WebUtility]::HtmlDecode($stripped)

    $normalized = $decoded -replace "[\r\n]+", "`n"
    $normalized = $normalized -replace "[ \t]+", " "
    $normalized = $normalized.Trim()

    $txtPath = Join-Path $outDir ($entry.Key -replace '\.php$', '.txt')
    Set-Content -Path $txtPath -Value $normalized -Encoding UTF8

    $htmlPath = Join-Path $outDir ($entry.Key -replace '\.php$', '.html')
    $lines = @(
        '<!doctype html>',
        '<html lang="en">',
        '<head>',
        '<meta charset="utf-8" />',
        "<title>$($entry.Value) - EduTrack</title>",
        '<style>',
        'body { font-family: "Times New Roman", serif; line-height: 1.5; margin: 2.5rem; color: #111; }',
        'h1 { font-size: 1.8rem; margin-bottom: 1rem; }',
        'pre { white-space: pre-wrap; font-family: "Times New Roman", serif; font-size: 1rem; }',
        '</style>',
        '</head>',
        '<body>',
        "<h1>$($entry.Value)</h1>",
        "<pre>$normalized</pre>",
        '</body>',
        '</html>'
    )
    $html = $lines -join "`n"
    Set-Content -Path $htmlPath -Value $html -Encoding UTF8
}
