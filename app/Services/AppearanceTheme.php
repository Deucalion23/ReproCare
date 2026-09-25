<?php

namespace App\Services;

use App\Models\Setting;

class AppearanceTheme
{
    /** Read only this public setting; never serialize clinical or gateway settings. */
    public function current(): array
    {
        try {
            // Avoid the legacy static Setting cache in long-running workers.
            $saved = json_decode(Setting::where('key', 'appearance.theme')->value('value') ?? '{}', true);
        } catch (\Throwable $e) {
            $saved = [];
        }
        $defaults = config('appearance.defaults');
        $theme = $defaults;
        foreach (config('appearance.options') as $slot => $allowed) {
            if (in_array($saved[$slot] ?? null, $allowed, true)) {
                $theme[$slot] = $saved[$slot];
            }
        }
        if ($theme['primary'] === $theme['secondary']) {
            $theme['secondary'] = $theme['primary'] === 'pink' ? 'purple' : 'pink';
        }
        $theme['mode'] = ($saved['mode'] ?? '') === 'dark' ? 'dark' : 'light';
        $theme['revision'] = is_string($saved['revision'] ?? null) ? $saved['revision'] : 'default';
        return $theme;
    }

    public function palette(): array
    {
        $palette = [];
        foreach (config('appearance.colors') as $name => $color) {
            $hex = $color['hex'];
            $hover = $this->mix($hex, '#101828', .14);
            $darkSoft = $this->mix($hex, '#1F2937', .83);
            $palette[$name] = $color + [
                'hover' => $hover,
                'on' => $this->onColor($hex),
                'on-hover' => $this->onColor($hover),
                'text-light' => $this->readable($hex, $color['soft'], '#101828'),
                'text-dark' => $this->readable($hex, $darkSoft, '#FFFFFF'),
                'soft-dark' => $darkSoft,
            ];
        }
        return $palette;
    }

    public function variables(array $theme): array
    {
        $palette = $this->palette();
        $vars = [];
        foreach (['primary', 'secondary', 'accent'] as $slot) {
            foreach ($palette[$theme[$slot]] as $variant => $value) {
                if ($variant !== 'label') {
                    $vars['--theme-'.$slot.($variant === 'hex' ? '' : '-'.$variant)] = $value;
                }
            }
        }
        return $vars;
    }

    public function contrast(string $a, string $b): float
    {
        $values = array_map(function ($hex) {
            $rgb = array_map(function ($channel) {
                $value = hexdec($channel) / 255;
                return $value <= .04045 ? $value / 12.92 : (($value + .055) / 1.055) ** 2.4;
            }, str_split(ltrim($hex, '#'), 2));
            return $rgb[0] * .2126 + $rgb[1] * .7152 + $rgb[2] * .0722;
        }, [$a, $b]);
        return (max($values) + .05) / (min($values) + .05);
    }

    private function onColor(string $hex): string
    {
        return $this->contrast($hex, '#FFFFFF') >= 4.5 ? '#FFFFFF' : $this->readable('#101828', $hex, '#000000');
    }

    private function readable(string $hex, string $background, string $target): string
    {
        for ($step = 0; $step <= 100; $step++) {
            $candidate = $this->mix($hex, $target, $step / 100);
            if ($this->contrast($candidate, $background) >= 4.5) {
                return $candidate;
            }
        }
        return $target;
    }

    private function mix(string $a, string $b, float $amount): string
    {
        $channels = [];
        for ($i = 1; $i < 7; $i += 2) {
            $channels[] = (int) round(hexdec(substr($a, $i, 2)) * (1 - $amount) + hexdec(substr($b, $i, 2)) * $amount);
        }
        return sprintf('#%02X%02X%02X', ...$channels);
    }
}
