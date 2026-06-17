<?php

namespace App\Services;

use App\Models\Person;
use App\Models\UniformItem;
use Illuminate\Support\Str;

class UniformSizeService
{
    public function suggestSize(Person $person, UniformItem $item): ?string
    {
        $type = $this->itemType($item);

        if (! $type) {
            return null;
        }

        $sizes = config("uniform_sizes.{$type}", []);

        if (! is_array($sizes) || $sizes === []) {
            return null;
        }

        $availableSizes = $this->availableSizes($item);

        foreach ($sizes as $size => $range) {
            if ($availableSizes !== [] && ! in_array((string) $size, $availableSizes, true)) {
                continue;
            }

            if ($this->matches($person, $range)) {
                return (string) $size;
            }
        }

        return $this->closestSize($person, $sizes, $availableSizes);
    }

    public function hasEnoughData(Person $person, UniformItem $item): bool
    {
        $type = $this->itemType($item);

        return match ($type) {
            'pants', 'boots' => $person->waist_cm !== null,
            'tshirt', 'jacket' => $person->chest_cm !== null,
            default => false,
        };
    }

    public function itemType(UniformItem $item): ?string
    {
        $text = Str::lower(Str::ascii($item->category.' '.$item->name));

        return match (true) {
            str_contains($text, 'calca'), str_contains($text, 'pants'), str_contains($text, 'trousers') => 'pants',
            str_contains($text, 'tshirt'), str_contains($text, 't-shirt'), str_contains($text, 'camisola'), str_contains($text, 'polo') => 'tshirt',
            str_contains($text, 'casaco'), str_contains($text, 'jacket'), str_contains($text, 'blusao') => 'jacket',
            str_contains($text, 'bota'), str_contains($text, 'boot') => 'boots',
            default => null,
        };
    }

    private function matches(Person $person, array $range): bool
    {
        foreach ($range as $key => $value) {
            [$measure, $bound] = explode('_', (string) $key, 2);
            $personValue = $person->{$measure.'_cm'} ?? null;

            if ($personValue === null) {
                return false;
            }

            if ($bound === 'min' && $personValue < $value) {
                return false;
            }

            if ($bound === 'max' && $personValue > $value) {
                return false;
            }
        }

        return true;
    }

    private function closestSize(Person $person, array $sizes, array $availableSizes = []): ?string
    {
        $bestSize = null;
        $bestDistance = null;

        foreach ($sizes as $size => $range) {
            if ($availableSizes !== [] && ! in_array((string) $size, $availableSizes, true)) {
                continue;
            }

            $distance = $this->rangeDistance($person, $range);

            if ($distance === null) {
                continue;
            }

            if ($bestDistance === null || $distance < $bestDistance) {
                $bestDistance = $distance;
                $bestSize = (string) $size;
            }
        }

        return $bestSize;
    }

    private function availableSizes(UniformItem $item): array
    {
        return $item->sizes()
            ->where('stock', '>', 0)
            ->pluck('size')
            ->map(fn ($size) => (string) $size)
            ->all();
    }

    private function rangeDistance(Person $person, array $range): ?float
    {
        $distance = 0;

        foreach ($range as $key => $value) {
            [$measure, $bound] = explode('_', (string) $key, 2);
            $personValue = $person->{$measure.'_cm'} ?? null;

            if ($personValue === null) {
                return null;
            }

            if ($bound === 'min' && $personValue < $value) {
                $distance += $value - $personValue;
            }

            if ($bound === 'max' && $personValue > $value) {
                $distance += $personValue - $value;
            }
        }

        return $distance;
    }
}
