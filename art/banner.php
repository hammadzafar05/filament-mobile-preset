<?php

/**
 * Social banner for hammadzafar05/filament-mobile-preset.
 *
 * Thesis: the banner IS the phone screen. A reachability sweep anchored at the
 * bottom-right corner — where a right thumb pivots — explains why the plugin puts
 * controls where it does. Rendered at 3x and downsampled, because GD does not
 * antialias arcs.
 *
 * Type: Bahnschrift (DIN-derived condensed grotesque; DIN is the face of technical
 * drawing and measurement, and thumb reach is a measurement) with Cascadia Mono
 * for anything a developer would actually type.
 */

const W = 1280;
const H = 640;
const S = 3;           // supersample factor

const FONT_DISPLAY = 'C:/Windows/Fonts/framd.ttf';
const FONT_MONO    = 'C:/Windows/Fonts/CascadiaMono.ttf';
const FONT_BODY    = 'C:/Windows/Fonts/segoeuil.ttf';

$im = imagecreatetruecolor(W * S, H * S);
imagealphablending($im, true);
imagesavealpha($im, true);

// ---- palette -------------------------------------------------------------
$rgb = fn (string $hex): array => [
    hexdec(substr($hex, 1, 2)),
    hexdec(substr($hex, 3, 2)),
    hexdec(substr($hex, 5, 2)),
];
$col = function (string $hex, int $alpha = 0) use ($im, $rgb) {
    [$r, $g, $b] = $rgb($hex);

    return imagecolorallocatealpha($im, $r, $g, $b, $alpha);
};

$INK        = '#08080C';  // near-black, faint violet cast — Filament's dark panel
$SURFACE    = '#15151D';
$EDGE       = '#26262F';
$AMBER      = '#F59E0B';  // Filament Color::Amber primary
$AMBER_LIT  = '#FCD34D';
$TEXT       = '#FAFAFA';
$MUTED      = '#8B8B96';
$FAR        = '#3A3A44';  // the strain zone: what is out of reach

imagefill($im, 0, 0, $col($INK));

// ---- helpers -------------------------------------------------------------
$px = fn (float $n): int => (int) round($n * S);

/** Rounded rectangle, optionally outlined instead of filled. */
$roundRect = function (float $x, float $y, float $w, float $h, float $r, $fill) use ($im, $px) {
    $x = $px($x); $y = $px($y); $w = $px($w); $h = $px($h); $r = $px($r);
    imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $fill);
    imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $fill);
    $d = $r * 2;
    imagefilledellipse($im, $x + $r, $y + $r, $d, $d, $fill);
    imagefilledellipse($im, $x + $w - $r, $y + $r, $d, $d, $fill);
    imagefilledellipse($im, $x + $r, $y + $h - $r, $d, $d, $fill);
    imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $d, $d, $fill);
};

/** Text on a baseline. Returns advance width in design px. */
$text = function (float $size, float $x, float $y, $color, string $font, string $str) use ($im, $px): float {
    $b = imagettftext($im, $size * S, 0, $px($x), $px($y), $color, $font, $str);

    return ($b[2] - $b[0]) / S;
};

/** Tracked text — GD has no letter-spacing, so advance manually. */
$tracked = function (float $size, float $x, float $y, $color, string $font, string $str, float $track) use ($im, $px): float {
    $cx = $x;
    foreach (preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        $b = imagettftext($im, $size * S, 0, $px($cx), $px($y), $color, $font, $ch);
        $cx += (($b[2] - $b[0]) / S) + $track;
        if ($ch === ' ') {
            $cx += $size * 0.28;
        }
    }

    return $cx - $x;
};

$measure = function (float $size, string $font, string $str) use ($px): float {
    $b = imagettfbbox($size * S, 0, $font, $str);

    return ($b[2] - $b[0]) / S;
};

// ---- the signature: thumb reach sweep ------------------------------------
// Anchored at the bottom-right corner, the pivot of a right thumb. Warmth rises
// as reach gets easier — a reachability heatmap, not decoration.
//
// Drawn largest to smallest in OPAQUE precomputed ink->amber blends. Stacking
// translucent pies would accumulate alpha in the overlap and blow out the centre.
$cx = W;
$cy = H;
$mix = function (float $t) use ($col, $rgb): int {
    [$ir, $ig, $ib] = $rgb('#08080C');
    [$ar, $ag, $ab] = $rgb('#F59E0B');

    return $col(sprintf('#%02X%02X%02X',
        (int) round($ir + ($ar - $ir) * $t),
        (int) round($ig + ($ag - $ig) * $t),
        (int) round($ib + ($ab - $ib) * $t),
    ));
};
// Outermost radius is capped at 950 so the band edge crosses the top edge right of
// the headline. At 1120 it cut a visible tonal step straight through "Mobile".
foreach ([[950, 0.035], [800, 0.062], [660, 0.098], [530, 0.15], [410, 0.215]] as [$r, $t]) {
    imagefilledarc($im, $px($cx), $px($cy), $px($r * 2), $px($r * 2), 180, 270, $mix($t), IMG_ARC_PIE);
}

// The boundary of comfortable one-handed use, drawn on a band edge so it reads as
// that band's contour. One crisp line does the explaining; the bands stay quiet.
for ($t = 0; $t < 4; $t++) {
    $rr = 530 + $t * 0.55;
    imagearc($im, $px($cx), $px($cy), $px($rr * 2), $px($rr * 2), 180, 270, $col($AMBER, 42));
}

// ---- right: the panel, cropped off the TOP -------------------------------
// Bleeding off the top rather than the bottom keeps the nav bar whole. That bar
// is the thing being demonstrated; clipping it would throw away the argument.
$panelX = 828;
$panelY = -72;
$panelW = 340;
$panelH = 632;                       // bottom lands at 560, clear of the canvas edge
$panelBottom = $panelY + $panelH;

$roundRect($panelX, $panelY, $panelW, $panelH, 28, $col($SURFACE));

// Stacked table cards — the plugin's stackedOnMobile() default. The first is
// clipped by the top edge so the list reads as continuing beyond the frame.
$cardY = 14;
for ($i = 0; $i < 4; $i++) {
    $roundRect($panelX + 18, $cardY, $panelW - 36, 100, 14, $col('#1C1C26'));
    $roundRect($panelX + 36, $cardY + 20, 48, 7, 3.5, $col($FAR));       // column label
    $roundRect($panelX + 36, $cardY + 38, 146, 10, 5, $col($MUTED));     // value
    // Row actions, right-aligned into thumb reach.
    foreach ([-128, -72] as $ox) {
        $bx = $panelX + $panelW + $ox;
        $roundRect($bx, $cardY + 62, 52, 24, 7, $col('#2C2214'));
        $roundRect($bx + 11, $cardY + 72, 30, 4, 2, $col($AMBER, 26));
    }
    $cardY += 116;
}

// Bottom nav — sits inside the innermost reach band, which is the whole point.
$navH = 88;
$navY = $panelBottom - $navH;
// Rounded at the bottom to follow the panel, squared off at the top. Drawing a
// short rounded rect for the bottom alone left its corner ellipses protruding.
$roundRect($panelX, $navY, $panelW, $navH, 28, $col('#0E0E16'));
imagefilledrectangle($im, $px($panelX), $px($navY), $px($panelX + $panelW), $px($navY + 30), $col('#0E0E16'));
imagefilledrectangle($im, $px($panelX), $px($navY), $px($panelX + $panelW), $px($navY + 1.5), $col($EDGE));
for ($i = 0; $i < 3; $i++) {
    $ix = $panelX + 57 + $i * 113;
    $on = $i === 1;
    $roundRect($ix - 13, $navY + 22, 26, 26, 8, $col($on ? $AMBER : $FAR));
    $roundRect($ix - 19, $navY + 58, 38, 6, 3, $col($on ? $AMBER : $FAR, $on ? 45 : 0));
}

// ---- left: the words -----------------------------------------------------
$L = 84;

$tracked(15, $L, 128, $col($AMBER), FONT_MONO, 'FILAMENT 5  ·  PANELS', 3.2);

imagettftext($im, 104 * S, 0, $px($L - 5), $px(252), $col($TEXT), FONT_DISPLAY, 'Mobile');
imagettftext($im, 104 * S, 0, $px($L - 5), $px(356), $col($AMBER), FONT_DISPLAY, 'Preset');

$roundRect($L, 396, 96, 5, 2.5, $col($AMBER));

imagettftext($im, 29 * S, 0, $px($L), $px(456), $col($MUTED), FONT_BODY, 'Every control within reach');
imagettftext($im, 29 * S, 0, $px($L), $px(496), $col($MUTED), FONT_BODY, 'of one thumb.');

// The developer's actual next action.
$cmd = 'composer require hammadzafar05/filament-mobile-preset';
$cmdW = $measure(15.5, FONT_MONO, $cmd);
$roundRect($L - 18, 546, $cmdW + 52, 52, 10, $col('#12121A'));
$roundRect($L - 18, 546, 3, 52, 1.5, $col($AMBER, 40));
imagettftext($im, 15.5 * S, 0, $px($L + 2), $px(578), $col('#7A7A88'), FONT_MONO, $cmd);

// ---- downsample ----------------------------------------------------------
$out = imagecreatetruecolor(W, H);
imagealphablending($out, false);
imagesavealpha($out, true);
imagecopyresampled($out, $im, 0, 0, 0, 0, W, H, W * S, H * S);

$target = $argv[1] ?? __DIR__ . '/banner.png';
imagepng($out, $target, 9);
echo "wrote {$target} (" . W . 'x' . H . ")\n";
