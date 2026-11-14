<?php
// Generate a simple placeholder poster image
header('Content-Type: image/svg+xml');
$w = $_GET['w'] ?? 300;
$h = $_GET['h'] ?? 450;
$w = min((int)$w, 600);
$h = min((int)$h, 900);
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="$w" height="$h" viewBox="0 0 $w $h">
  <rect width="$w" height="$h" fill="#1b2a3b"/>
  <rect x="10" y="10" width="<?= $w-20 ?>" height="<?= $h-20 ?>" fill="none" stroke="#253447" stroke-width="2" rx="8"/>
  <text x="50%" y="45%" font-family="sans-serif" font-size="48" fill="#253447" text-anchor="middle">🎬</text>
  <text x="50%" y="60%" font-family="sans-serif" font-size="14" fill="#8899aa" text-anchor="middle">No Poster</text>
</svg>
SVG;
