param(
    [ValidateSet('webp', 'avif')]
    [string] $Format = 'webp'
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$imagesRoot = Join-Path $projectRoot 'public/images'

if (-not (Get-Command magick -ErrorAction SilentlyContinue)) {
    Write-Error 'ImageMagick est requis. Installez-le puis relancez ce script.'
    exit 1
}

if (-not (Test-Path $imagesRoot)) {
    Write-Error "Dossier introuvable : $imagesRoot"
    exit 1
}

# Chaque règle définit la taille maximale et la qualité adaptées à l'usage.
$rules = @(
    @{ Pattern = 'hero-*.jpg'; Width = 1800; Height = 900; Quality = 78 },
    @{ Pattern = 'restauration.jpg'; Width = 1800; Height = 700; Quality = 78 },
    @{ Pattern = 'plan-apercu.png'; Width = 1200; Height = 700; Quality = 80 },
    @{ Pattern = 'demo/*.jpg'; Width = 900; Height = 700; Quality = 78 },
    @{ Pattern = 'shops/*.jpg'; Width = 900; Height = 700; Quality = 78 },
    @{ Pattern = 'resto/*.jpg'; Width = 900; Height = 700; Quality = 78 },
    @{ Pattern = 'promo/*.jpg'; Width = 1000; Height = 800; Quality = 78 },
    @{ Pattern = 'news/*.jpg'; Width = 1000; Height = 700; Quality = 78 }
)

$files = Get-ChildItem $imagesRoot -Recurse -File | Where-Object {
    $_.Extension.ToLowerInvariant() -in @('.jpg', '.jpeg', '.png') -and
    $_.FullName -notmatch '[\\/]logo[\\/]'
}

$converted = 0
$skipped = 0

foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($imagesRoot.Length + 1).Replace('\', '/')
    $rule = $rules | Where-Object {
        $pattern = $_.Pattern.Replace('/', '\\')
        $relativePath -like $pattern
    } | Select-Object -First 1

    if (-not $rule) {
        $skipped++
        continue
    }

    $outputPath = [System.IO.Path]::ChangeExtension($file.FullName, ".${Format}")
    $resize = '{0}x{1}>' -f $rule.Width, $rule.Height
    $arguments = @(
        $file.FullName,
        '-auto-orient',
        '-strip',
        '-resize', $resize,
        '-quality', $rule.Quality,
        $outputPath
    )

    if ($Format -eq 'webp') {
        $arguments = @($file.FullName, '-auto-orient', '-strip', '-resize', $resize, '-quality', $rule.Quality, '-define', 'webp:method=6', $outputPath)
    }

    & magick @arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Échec de conversion : $relativePath"
    }

    $originalSize = [math]::Round($file.Length / 1KB, 1)
    $optimizedSize = [math]::Round((Get-Item $outputPath).Length / 1KB, 1)
    Write-Host ("{0} -> {1} KB vers {2} KB" -f $relativePath, $originalSize, $optimizedSize)
    $converted++
}

Write-Host "Conversion terminée : $converted fichier(s) généré(s) en .$Format."
Write-Host "Fichier(s) ignoré(s) : $skipped. Les originaux ont été conservés."
