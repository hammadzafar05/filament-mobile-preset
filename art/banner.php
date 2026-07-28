<?php

/**
 * Social / directory banner for hammadzafar05/filament-mobile-preset.
 *
 * Thesis: the banner IS the phone screen. A reachability sweep anchored at the
 * bottom-right corner — where a right thumb pivots — explains why the plugin puts
 * controls where it does. The panel is cropped off the top, never the bottom, so
 * the nav bar it is arguing for stays whole.
 *
 * Type: Franklin Gothic Medium for display, Cascadia Mono for anything a developer
 * would actually type. Rendered supersampled then downsampled, because GD does not
 * antialias arcs.
 *
 * Usage:  php art/banner.php <out> [light|dark] [scale] [jpg|png]
 *
 * Filament plugin directory — 16:9, >=2560x1440, JPEG, light theme:
 *   php art/banner.php art/banner-light.jpg light 2 jpg
 * GitHub social preview:
 *   php art/banner.php art/banner-dark.jpg dark 2 jpg
 */

const DW = 1280;   // design space, 16:9
const DH = 720;
const SS = 2;      // supersample beyond the output scale

$out    = $argv[1] ?? __DIR__ . '/banner-light.jpg';
$theme  = $argv[2] ?? 'light';
$scale  = (float) ($argv[3] ?? 2);
$format = $argv[4] ?? (str_ends_with($out, '.png') ? 'png' : 'jpg');

$W = (int) round(DW * $scale);
$H = (int) round(DH * $scale);
$S = $scale * SS;
$FW = (int) round(DW * $S);
$FH = (int) round(DH * $S);

$FONT_DISPLAY = 'C:/Windows/Fonts/framd.ttf';
$FONT_MONO    = 'C:/Windows/Fonts/CascadiaMono.ttf';
$FONT_BODY    = 'C:/Windows/Fonts/segoeuil.ttf';

// ---- palettes ------------------------------------------------------------
// Light is the Filament directory's house style; dark matches the panel this
// plugin actually ships into. Amber is Filament's own Color::Amber either way.
$P = $theme === 'dark' ? [
    'bg' => '#08080C', 'panel' => '#15151D', 'card' => '#1C1C26', 'nav' => '#0E0E16',
    'edge' => '#26262F', 'text' => '#FAFAFA', 'muted' => '#8B8B96', 'far' => '#3A3A44',
    'btn' => '#2C2214', 'pill' => '#12121A', 'pillText' => '#7A7A88',
    'amber' => '#F59E0B', 'bands' => [0.035, 0.062, 0.098, 0.15, 0.215], 'arcA' => 42,
] : [
    'bg' => '#FAFAF9', 'panel' => '#FFFFFF', 'card' => '#F4F4F5', 'nav' => '#FFFFFF',
    'edge' => '#E4E4E7', 'text' => '#18181B', 'muted' => '#71717A', 'far' => '#D4D4D8',
    'btn' => '#FEF3C7', 'pill' => '#F4F4F5', 'pillText' => '#52525B',
    'amber' => '#F59E0B', 'bands' => [0.05, 0.09, 0.14, 0.20, 0.28], 'arcA' => 24,
];

$im = imagecreatetruecolor($FW, $FH);
imagealphablending($im, true);
imagesavealpha($im, true);

$rgb = fn (string $hex): array => [
    hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)),
];
$col = function (string $hex, int $alpha = 0) use ($im, $rgb) {
    [$r, $g, $b] = $rgb($hex);

    return imagecolorallocatealpha($im, $r, $g, $b, $alpha);
};
$px = fn (float $n): int => (int) round($n * $S);

imagefilledrectangle($im, 0, 0, $FW, $FH, $col($P['bg']));

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

// GD has no letter-spacing, so advance manually.
$tracked = function (float $size, float $x, float $y, $color, string $font, string $str, float $track) use ($im, $px, $S) {
    $cx = $x;
    foreach (preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        $b = imagettftext($im, $size * $S, 0, $px($cx), $px($y), $color, $font, $ch);
        $cx += (($b[2] - $b[0]) / $S) + $track;
        if ($ch === ' ') {
            $cx += $size * 0.28;
        }
    }
};

$measure = fn (float $size, string $font, string $str): float
    => (imagettfbbox($size * $S, 0, $font, $str)[2] - imagettfbbox($size * $S, 0, $font, $str)[0]) / $S;

$write = function (float $size, float $x, float $y, $color, string $font, string $str) use ($im, $px, $S) {
    imagettftext($im, $size * $S, 0, $px($x), $px($y), $color, $font, $str);
};

// ---- signature: the thumb reach sweep ------------------------------------
// Opaque precomputed bg->amber blends, largest to smallest. Stacking translucent
// pies accumulates alpha in the overlap and blows the centre out to solid orange.
$cx = DW;
$cy = DH;
$mix = function (float $t) use ($col, $rgb, $P): int {
    [$br, $bg, $bb] = $rgb($P['bg']);
    [$ar, $ag, $ab] = $rgb($P['amber']);

    return $col(sprintf('#%02X%02X%02X',
        (int) round($br + ($ar - $br) * $t),
        (int) round($bg + ($ag - $bg) * $t),
        (int) round($bb + ($ab - $bb) * $t),
    ));
};
// Outermost radius clears the headline: at 1000 the band edge crosses the top
// edge near x=586, right of where "Mobile" ends.
foreach ([1000, 850, 700, 560, 430] as $i => $r) {
    imagefilledarc($im, $px($cx), $px($cy), $px($r * 2), $px($r * 2), 180, 270, $mix($P['bands'][$i]), IMG_ARC_PIE);
}
// The boundary of comfortable one-handed use, drawn on a band edge so it reads as
// that band's contour rather than a stray line.
for ($t = 0; $t < 4; $t++) {
    $rr = 560 + $t * 0.55;
    imagearc($im, $px($cx), $px($cy), $px($rr * 2), $px($rr * 2), 180, 270, $col($P['amber'], $P['arcA']));
}

// ---- right: the panel, cropped off the top -------------------------------
$panelX = 828;
$panelY = -72;
$panelW = 340;
$panelH = 712;                        // bottom lands at 640, clear of the canvas edge
$panelBottom = $panelY + $panelH;

if ($theme !== 'dark') {
    $roundRect($panelX - 1, $panelY, $panelW + 2, $panelH + 1, 29, $col($P['edge']));
}
$roundRect($panelX, $panelY, $panelW, $panelH, 28, $col($P['panel']));

// Stacked table cards — the plugin's stackedOnMobile() default. The first is
// clipped by the top edge so the list reads as continuing past the frame.
$cardY = 14;
for ($i = 0; $i < 5; $i++) {
    $roundRect($panelX + 18, $cardY, $panelW - 36, 100, 14, $col($P['card']));
    $roundRect($panelX + 36, $cardY + 20, 48, 7, 3.5, $col($P['far']));    // column label
    $roundRect($panelX + 36, $cardY + 38, 146, 10, 5, $col($P['muted']));  // value
    foreach ([-128, -72] as $ox) {                                          // row actions, in reach
        $bx = $panelX + $panelW + $ox;
        $roundRect($bx, $cardY + 62, 52, 24, 7, $col($P['btn']));
        $roundRect($bx + 11, $cardY + 72, 30, 4, 2, $col($P['amber'], 26));
    }
    $cardY += 116;
}

// Bottom nav — sits inside the innermost reach band, which is the whole point.
// Rounded at the bottom to follow the panel, squared off at the top.
$navH = 88;
$navY = $panelBottom - $navH;
$roundRect($panelX, $navY, $panelW, $navH, 28, $col($P['nav']));
imagefilledrectangle($im, $px($panelX), $px($navY), $px($panelX + $panelW), $px($navY + 30), $col($P['nav']));
imagefilledrectangle($im, $px($panelX), $px($navY), $px($panelX + $panelW), $px($navY + 1.5), $col($P['edge']));
for ($i = 0; $i < 3; $i++) {
    $ix = $panelX + 57 + $i * 113;
    $on = $i === 1;
    $roundRect($ix - 13, $navY + 22, 26, 26, 8, $col($on ? $P['amber'] : $P['far']));
    $roundRect($ix - 19, $navY + 58, 38, 6, 3, $col($on ? $P['amber'] : $P['far'], $on ? 45 : 0));
}

// ---- left: the words -----------------------------------------------------
$L = 84;
$tracked(15, $L, 140, $col($P['amber']), $FONT_MONO, 'FILAMENT 5  ·  PANELS', 3.2);
$write(104, $L - 5, 278, $col($P['text']), $FONT_DISPLAY, 'Mobile');
$write(104, $L - 5, 386, $col($P['amber']), $FONT_DISPLAY, 'Preset');
$roundRect($L, 426, 96, 5, 2.5, $col($P['amber']));
$write(29, $L, 496, $col($P['muted']), $FONT_BODY, 'Every control within reach');
$write(29, $L, 536, $col($P['muted']), $FONT_BODY, 'of one thumb.');

// The reader's actual next action, which earns its place over a feature list
// that would be illegible at directory thumbnail size.
$cmd = 'composer require hammadzafar05/filament-mobile-preset';
$cmdW = $measure(15.5, $FONT_MONO, $cmd);
$roundRect($L - 18, 592, $cmdW + 52, 52, 10, $col($P['pill']));
$roundRect($L - 18, 592, 3, 52, 1.5, $col($P['amber'], 40));
$write(15.5, $L + 2, 624, $col($P['pillText']), $FONT_MONO, $cmd);

// ---- downsample ----------------------------------------------------------
$dst = imagecreatetruecolor($W, $H);
imagealphablending($dst, false);
imagesavealpha($dst, true);
imagecopyresampled($dst, $im, 0, 0, 0, 0, $W, $H, $FW, $FH);

$format === 'png' ? imagepng($dst, $out, 9) : imagejpeg($dst, $out, 92);

echo "wrote {$out}  {$W}x{$H}  {$theme}/{$format}\n";
