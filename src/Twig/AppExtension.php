<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(name: 'hexdec', callable: [$this, 'hexdecFilter']),
            new TwigFilter(name: 'contrastColor', callable: [$this, 'contrastColor']),
        ];
    }

    public function hexdecFilter(string $hex): float|int
    {
        return hexdec(hex_string: $hex);
    }

    public function contrastColor(string $color): string
    {
        $r = hexdec(substr(string: $color, offset: 1, length: 2));
        $g = hexdec(substr(string: $color, offset: 3, length: 2));
        $b = hexdec(substr(string: $color, offset: 5, length: 2));
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $brightness > 128 ? '#000000' : '#FFFFFF';
    }
}
