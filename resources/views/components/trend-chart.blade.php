@props([
    'data' => [],
    'labels' => [],
    'color' => 'var(--gold)',
    'height' => 200,
])

@php
    $values = array_map('floatval', array_values((array) $data));
    $n = count($values);

    if ($n < 2) {
        $values = $n === 1 ? [$values[0], $values[0]] : [0, 0];
        $n = 2;
    }

    $w = 100;
    $h = 40;
    $max = max($values);
    $span = $max > 0 ? $max : 1;

    $points = [];
    foreach ($values as $i => $value) {
        $x = ($i / ($n - 1)) * $w;
        $y = $h - ($value / $span) * ($h - 4) - 2;
        $points[] = round($x, 2).','.round($y, 2);
    }

    $polyline = implode(' ', $points);
    $area = '0,'.$h.' '.$polyline.' '.$w.','.$h;
    $uid = 'trend-'.\Illuminate\Support\Str::random(6);

    $countLbl = count($labels);
    $showLabels = $labels;
    if ($countLbl > 8) {
        $step = (int) ceil($countLbl / 6);
        if ($step < 2) {
            $step = 2;
        }
        $subset = [];
        for ($i = 0; $i < $countLbl; $i += $step) {
            $subset[] = $labels[$i];
        }
        if (end($subset) !== $labels[$countLbl - 1]) {
            $subset[] = $labels[$countLbl - 1];
        }
        if (count($subset) > 1 && $subset[count($subset) - 1] === $subset[count($subset) - 2]) {
            array_pop($subset);
        }
        $showLabels = $subset;
    }
@endphp

<div>
    <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" style="display:block;width:100%;height:{{ $height }}px" aria-hidden="true">
        <defs>
            <linearGradient id="{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.22" />
                <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0" />
            </linearGradient>
        </defs>
        <polygon points="{{ $area }}" fill="url(#{{ $uid }})" />
        <polyline points="{{ $polyline }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
    </svg>

    @if (! empty($showLabels))
        <div class="chart-labels">
            @foreach ($showLabels as $label)
                <span>{{ $label }}</span>
            @endforeach
        </div>
    @endif
</div>
