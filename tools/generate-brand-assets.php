<?php

/**
 * Planora brand asset generator.
 *
 * Derives every icon asset from the single master logo file kept at
 * public/images/planora-logo.png, so the whole system only ever needs one image.
 *
 * Usage:  php tools/generate-brand-assets.php
 *
 * Output:
 *   public/images/planora-logo.png     normalised master (real PNG, max 512px)
 *   public/images/planora-logo-sm.png  128x128 tile used by every brand mark
 *   public/favicon.ico                 multi-size 16/32/48 icon
 *   public/favicon.png                 64x64 icon
 *   public/apple-touch-icon.png        180x180 icon
 *
 * The original file is never destroyed: when the master needs converting or
 * resizing it is copied to public/images/planora-logo-original.<ext> first.
 *
 * Requires the GD extension (bundled with XAMPP).
 */

declare(strict_types=1);

const MASTER_MAX = 512;
const TILE_SIZE = 128;
const FAVICON_SIZES = [16, 32, 48];
const FAVICON_PNG_SIZE = 64;
const APPLE_TOUCH_SIZE = 180;

$root = dirname(__DIR__);
$publicDir = $root . DIRECTORY_SEPARATOR . 'public';
$imagesDir = $publicDir . DIRECTORY_SEPARATOR . 'images';
$masterPath = $imagesDir . DIRECTORY_SEPARATOR . 'planora-logo.png';

function fail(string $message): never
{
    fwrite(STDERR, '[FAIL] ' . $message . PHP_EOL);
    exit(1);
}

function ok(string $message): void
{
    fwrite(STDOUT, '[ OK ] ' . $message . PHP_EOL);
}

/** Create a truecolour canvas that keeps alpha transparency. */
function canvas(int $width, int $height): GdImage
{
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
    imagefilledrectangle($image, 0, 0, $width, $height, $transparent);

    return $image;
}

/** Read the master file into a GD image regardless of its on-disk extension. */
function load_image(string $path, string $mime): GdImage
{
    $image = match ($mime) {
        'image/png' => imagecreatefrompng($path),
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/gif' => imagecreatefromgif($path),
        'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
        default => false,
    };

    if (! $image instanceof GdImage) {
        fail('Unsupported image type: ' . ($mime !== '' ? $mime : 'unknown'));
    }

    return $image;
}

/** Scale into an exact box, preserving transparency. */
function resize(GdImage $source, int $width, int $height): GdImage
{
    $target = canvas($width, $height);
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

    return $target;
}

/** Centre-crop to a square and scale down in steps — used for the favicons. */
function square(GdImage $source, int $size): GdImage
{
    $image = $source;

    // Progressive halving keeps small icons crisp instead of aliased.
    while (min(imagesx($image), imagesy($image)) > $size * 2) {
        $image = resize($image, (int) round(imagesx($image) / 2), (int) round(imagesy($image) / 2));
    }

    $side = min(imagesx($image), imagesy($image));
    $offsetX = (int) ((imagesx($image) - $side) / 2);
    $offsetY = (int) ((imagesy($image) - $side) / 2);

    $target = canvas($size, $size);
    imagecopyresampled($target, $image, 0, 0, $offsetX, $offsetY, $size, $size, $side, $side);

    return $target;
}

/** Encode a GD image to PNG bytes without touching the filesystem. */
function png_bytes(GdImage $image): string
{
    ob_start();
    imagepng($image, null, 9);
    $bytes = ob_get_clean();

    if (! is_string($bytes) || $bytes === '') {
        fail('Failed to encode a PNG in memory.');
    }

    return $bytes;
}

/** Report whether the master has see-through pixels (sampled on a ~100x100 grid). */
function transparency_report(GdImage $image): string
{
    $width = imagesx($image);
    $height = imagesy($image);
    $stepX = max(1, (int) ($width / 100));
    $stepY = max(1, (int) ($height / 100));
    $samples = 0;
    $transparent = 0;

    for ($x = 0; $x < $width; $x += $stepX) {
        for ($y = 0; $y < $height; $y += $stepY) {
            $samples++;

            if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 4) {
                $transparent++;
            }
        }
    }

    if ($transparent === 0) {
        return sprintf(
            'opaque (%d samples, none see-through) — on the dark teal sidebar the logo shows as a light plate',
            $samples
        );
    }

    return sprintf('transparent (%d of %d sampled pixels are see-through)', $transparent, $samples);
}

/** Write a multi-size ICO container using embedded PNG payloads. */
function write_ico(string $path, array $payloads): void
{
    $count = count($payloads);
    $header = pack('vvv', 0, 1, $count);
    $directory = '';
    $data = '';
    $offset = 6 + ($count * 16);

    foreach ($payloads as $size => $bytes) {
        $dimension = $size >= 256 ? 0 : $size;
        $directory .= pack('CCCCvvVV', $dimension, $dimension, 0, 0, 1, 32, strlen($bytes), $offset);
        $data .= $bytes;
        $offset += strlen($bytes);
    }

    if (file_put_contents($path, $header . $directory . $data) === false) {
        fail('Cannot write: ' . $path);
    }
}

// ── 1. Sanity checks ─────────────────────────────────────────────────────
if (! extension_loaded('gd')) {
    fail('The GD extension is required but is not loaded.');
}

if (! is_dir($imagesDir) && ! mkdir($imagesDir, 0775, true) && ! is_dir($imagesDir)) {
    fail('Cannot create directory: ' . $imagesDir);
}

if (! is_file($masterPath) || filesize($masterPath) === 0) {
    fail('Master logo not found or empty: ' . $masterPath . PHP_EOL
        . '       Save the logo at that exact path, then run this script again.');
}

$info = @getimagesize($masterPath);

if ($info === false) {
    fail('Cannot read the master logo — is it really an image?');
}

$mime = $info['mime'] ?? '';
$width = (int) $info[0];
$height = (int) $info[1];
$master = load_image($masterPath, $mime);

ok('Master background: ' . transparency_report($master));

// ── 2. Normalise the master: real PNG, sensible dimensions ───────────────
$longestSide = max($width, $height);
$needsReencode = $mime !== 'image/png';
$needsResize = $longestSide > MASTER_MAX;

if ($needsReencode || $needsResize) {
    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'png',
    };
    $backupPath = $imagesDir . DIRECTORY_SEPARATOR . 'planora-logo-original.' . $extension;

    if (! is_file($backupPath) && ! copy($masterPath, $backupPath)) {
        fail('Cannot back up the original logo to: ' . $backupPath);
    }

    $scale = $needsResize ? MASTER_MAX / $longestSide : 1;
    $scaled = resize($master, (int) round($width * $scale), (int) round($height * $scale));

    if (file_put_contents($masterPath, png_bytes($scaled)) === false) {
        fail('Cannot write: ' . $masterPath);
    }

    ok(sprintf(
        'Master normalised: %dx%d %s -> %dx%d PNG (original kept as %s)',
        $width, $height, $mime, imagesx($scaled), imagesy($scaled), basename($backupPath)
    ));

    $master = $scaled;
} else {
    ok(sprintf('Master already optimised (%dx%d PNG) — left untouched.', $width, $height));
}

// ── 3. Favicon PNG ───────────────────────────────────────────────────────
$iconBytes = png_bytes(square($master, FAVICON_PNG_SIZE));
$iconPath = $publicDir . DIRECTORY_SEPARATOR . 'favicon.png';

if (file_put_contents($iconPath, $iconBytes) === false) {
    fail('Cannot write: ' . $iconPath);
}

ok('Wrote public/favicon.png (' . FAVICON_PNG_SIZE . 'x' . FAVICON_PNG_SIZE . ', ' . strlen($iconBytes) . ' bytes)');

// Brand-mark tile: the small optimised image served inside every .brand-mark.
$tileBytes = png_bytes(square($master, TILE_SIZE));
$tilePath = $imagesDir . DIRECTORY_SEPARATOR . 'planora-logo-sm.png';

if (file_put_contents($tilePath, $tileBytes) === false) {
    fail('Cannot write: ' . $tilePath);
}

ok('Wrote public/images/planora-logo-sm.png (' . TILE_SIZE . 'x' . TILE_SIZE . ', ' . round(strlen($tileBytes) / 1024, 1) . ' KB)');

// iOS home screen icon.
$touchBytes = png_bytes(square($master, APPLE_TOUCH_SIZE));
$touchPath = $publicDir . DIRECTORY_SEPARATOR . 'apple-touch-icon.png';

if (file_put_contents($touchPath, $touchBytes) === false) {
    fail('Cannot write: ' . $touchPath);
}

ok('Wrote public/apple-touch-icon.png (' . APPLE_TOUCH_SIZE . 'x' . APPLE_TOUCH_SIZE . ', ' . round(strlen($touchBytes) / 1024, 1) . ' KB)');

// ── 4. Favicon ICO ───────────────────────────────────────────────────────
$payloads = [];

foreach (FAVICON_SIZES as $size) {
    $payloads[$size] = png_bytes(square($master, $size));
}

$icoPath = $publicDir . DIRECTORY_SEPARATOR . 'favicon.ico';
write_ico($icoPath, $payloads);

ok('Wrote public/favicon.ico (' . implode('/', FAVICON_SIZES) . ' — ' . filesize($icoPath) . ' bytes)');
ok('Done. The system logo is public/images/planora-logo.png — replace that single file and re-run this script to refresh the icons.');
