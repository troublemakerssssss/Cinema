<?php
// Generate a simple placeholder poster image
header('Content-Type: image/svg+xml');
$w = $_GET['w'] ?? 300;
$h = $_GET['h'] ?? 450;
$w = min((int)$w, 600);
$h = min((int)$h, 900);
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$w" height="$h" viewBox="0 0 $w $h">
  <rect width="$w" height="$h" fill="#F5F6FA"/>
  <rect x="10" y="10" width="<?= $w-20 ?>" height="<?= $h-20 ?>" fill="none" stroke="#E8EAEF" stroke-width="2" rx="12"/>
  <text x="50%" y="45%" font-family="sans-serif" font-size="48" fill="#D5D8E0" text-anchor="middle">🎬</text>
  <text x="50%" y="60%" font-family="sans-serif" font-size="14" fill="#8B90A0" text-anchor="middle">No Poster</text>
</svg>
SVG;
