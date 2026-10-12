$ErrorActionPreference = 'Stop'
$f = Join-Path $PSScriptRoot 'resources\views\welcome.blade.php'
if (-not (Test-Path $f)) { throw "File not found: $f" }

$c = [System.IO.File]::ReadAllText($f, [System.Text.Encoding]::UTF8)
$orig = $c

function Get-IconFile([string]$txt) {
    if     ($txt -match 'All')      { return 'all' }
    if     ($txt -match 'Beach')    { return 'beach' }
    if     ($txt -match 'Festival') { return 'festival' }
    if     ($txt -match 'Landmark') { return 'landmark' }
    if     ($txt -match 'Shopping') { return 'shopping' }
    if     ($txt -match 'Street')   { return 'streets' }
    return 'tourist'
}

$chipPattern  = '(?s)<span class="place-filter-label">(.*?)</span>'
$badgePattern = '(?s)<span class="gallery-cat" aria-hidden="true">(.*?)</span>'

$c = [regex]::Replace($c, $chipPattern, [System.Text.RegularExpressions.MatchEvaluator]{
    param($m)
    $inner = $m.Groups[1].Value
    if ($inner -match '<img') { return $m.Value }
    $txt = ($inner -replace '^[^\x00-\x7F]+\s*', '').Trim()
    if ($txt.Length -eq 0) { return $m.Value }
    $file = Get-IconFile $txt
    ('<span class="place-filter-label"><img class="cat-icon" src="/images/icons/' + $file + '.png" alt="" aria-hidden="true"> ' + $txt + '</span>')
})

$c = [regex]::Replace($c, $badgePattern, [System.Text.RegularExpressions.MatchEvaluator]{
    param($m)
    $inner = $m.Groups[1].Value
    if ($inner -match '<img') { return $m.Value }
    $txt = ($inner -replace '^[^\x00-\x7F]+\s*', '').Trim()
    if ($txt.Length -eq 0) { return $m.Value }
    $file = Get-IconFile $txt
    ('<span class="gallery-cat" aria-hidden="true"><img class="cat-icon" src="/images/icons/' + $file + '.png" alt=""> ' + $txt + '</span>')
})

if ($c -ne $orig) {
    [System.IO.File]::WriteAllText($f, $c, (New-Object System.Text.UTF8Encoding($false)))
    Write-Output 'ICONS-REPLACED'
} else {
    Write-Output 'NO-CHANGE'
}
