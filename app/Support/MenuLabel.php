<?php

namespace App\Support;

class MenuLabel
{
    private const PRIMARY_ITEMS = [
        'NY',
        'NY Sumo',
        'Fillet Mignon',
        'Fillet Mignon Sumo',
        'Rib Eye',
        'Rib Eye Sumo',
        'A5 Wagyu',
        'Tomahawk',
        'Chicken',
        'Shrimp',
        'Lobster',
        'Scallops',
        'Salmon',
        'Ahi Tuna',
        'Halibu',
        'Tofu',
        'Vegetarian',
        'Salad',
        'Edamame',
        'Asparagus',
        'Noodles',
        'Gyoza',
        'Soup',
        'Fried Rice',
        'White Rice',
    ];

    private const REPLACEMENTS = [
        '/\bNY\s+Steak\b/i' => 'NY',
        '/\bNY\s+Sumo\b/i' => 'NY Sumo',
        '/\bFil(?:e|l)\s*t\s*Mignon\s+Sumo\b/i' => 'Fillet Mignon Sumo',
        '/\bFil(?:e|l)\s*t\s*Mignon\b/i' => 'Fillet Mignon',
        '/\bRib[-\s]*Eye\s+Sumo\b/i' => 'Rib Eye Sumo',
        '/\bRib[-\s]*Eye\b/i' => 'Rib Eye',
        '/\bA5\s+Wagyu\b/i' => 'A5 Wagyu',
        '/\bTomahawk\b/i' => 'Tomahawk',
        '/\bChicken\b/i' => 'Chicken',
        '/\bShrimp\b/i' => 'Shrimp',
        '/\bScallops?\b/i' => 'Scallops',
        '/\bSalmon\b/i' => 'Salmon',
        '/\bAhi\s+Tuna\b/i' => 'Ahi Tuna',
        '/(?<!Ahi\s)\bTuna\b/i' => 'Ahi Tuna',
        '/\bHalibut\b/i' => 'Halibu',
        '/\bHalibu\b/i' => 'Halibu',
        '/\bLobster\s+Tail\b/i' => 'Lobster',
        '/\bLobster\b/i' => 'Lobster',
        '/\bNoodles\b/i' => 'Noodles',
        '/\bSoup\b/i' => 'Soup',
        '/\bF\s*Rice\b/i' => 'Fried Rice',
        '/\bFried\s+Rice\b/i' => 'Fried Rice',
        '/\bWhite\s+Rice\b/i' => 'White Rice',
        '/\bW\s*Rice\b/i' => 'White Rice',
        '/\bSalad\b/i' => 'Salad',
        '/\bEdamame\b/i' => 'Edamame',
        '/\bAsparagus\b/i' => 'Asparagus',
        '/\bGyoza\b/i' => 'Gyoza',
        '/\bTofu\b/i' => 'Tofu',
        '/\bVegetarian\b/i' => 'Vegetarian',
        '/\bNY\b/i' => 'NY',
    ];

    public static function primaryItems(): array
    {
        return self::PRIMARY_ITEMS;
    }

    public static function standardize(?string $value): string
    {
        return self::standardizeText($value);
    }

    public static function standardizeText(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $result = (string) $value;
        foreach (self::REPLACEMENTS as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        // Collapse multiple spaces created by replacements and trim
        $result = preg_replace('/\s{2,}/', ' ', $result);

        return trim($result);
    }
}
