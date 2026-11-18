<?php

// Genera los iconos PWA (app icon con motivo de copa de whisky)
function makeIcon(int $size, string $path): void
{
    $im = imagecreatetruecolor($size, $size);

    // Fondo oscuro elegante con degradado
    $top = imagecolorallocate($im, 20, 17, 12);
    $bottom = imagecolorallocate($im, 12, 11, 9);
    for ($y = 0; $y < $size; $y++) {
        $t = $y / ($size - 1);
        $r = (int) (20 + (12 - 20) * $t);
        $g = (int) (17 + (11 - 17) * $t);
        $b = (int) (12 + (9 - 12) * $t);
        $c = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $y, $size, $y, $c);
    }

    // Borde dorado sutil (redondeado)
    $gold = imagecolorallocate($im, 184, 150, 74);
    $goldDim = imagecolorallocate($im, 138, 110, 42);

    // Copa de whisky estilizada (centrada)
    $cx = $size / 2;
    $baseW = $size * 0.42;      // ancho de la base
    $topW = $size * 0.52;       // ancho superior (copa invertida)
    $glassTop = $size * 0.30;
    $glassBottom = $size * 0.72;
    $stemY = $size * 0.72;

    // Cuerpo de la copa (trapecio invertido, estilo vaso de cóctel)
    $pts = [
        $cx - $topW / 2, $glassTop,
        $cx + $topW / 2, $glassTop,
        $cx + $baseW / 2, $glassBottom,
        $cx - $baseW / 2, $glassBottom,
    ];
    imagefilledpolygon($im, $pts, $goldDim);

    // Borde superior de la copa
    $barTop = $size * 0.27;
    $barH = $size * 0.045;
    imagefilledrectangle($im, (int) ($cx - $topW / 2), (int) $barTop, (int) ($cx + $topW / 2), (int) ($barTop + $barH), $gold);

    // Líquido (destellos dorados) dentro de la copa
    $liq = imagecolorallocate($im, 216, 176, 96);
    $liqY = $size * 0.52;
    $liqPts = [
        $cx - $topW / 2 + $size * 0.02, $liqY,
        $cx + $topW / 2 - $size * 0.02, $liqY,
        $cx + $baseW / 2 - $size * 0.02, $glassBottom,
        $cx - $baseW / 2 + $size * 0.02, $glassBottom,
    ];
    imagefilledpolygon($im, $liqPts, $liq);

    // Pie / base de la copa
    $stemX1 = $cx - $size * 0.04;
    $stemX2 = $cx + $size * 0.04;
    imagefilledrectangle($im, (int) $stemX1, (int) $glassBottom, (int) $stemX2, (int) ($glassBottom + $size * 0.08), $goldDim);
    $footY = $glassBottom + $size * 0.08;
    $footPts = [
        $cx - $size * 0.18, $footY,
        $cx + $size * 0.18, $footY,
        $cx + $size * 0.13, $footY + $size * 0.045,
        $cx - $size * 0.13, $footY + $size * 0.045,
    ];
    imagefilledpolygon($im, $footPts, $gold);

    // Guardar
    imagepng($im, $path);
    imagedestroy($im);
    echo "Generado: $path\n";
}

$base = __DIR__.'/../public';

makeIcon(192, $base.'/pwa-192x192.png');
makeIcon(512, $base.'/pwa-512x512.png');
makeIcon(180, $base.'/apple-touch-icon.png');
