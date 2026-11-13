<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize Indonesian phone numbers to canonical 62xxxxxxxx format.
     */
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Return common format variants (62..., 0..., +62...) for database matching.
     *
     * @return list<string>
     */
    public static function variants(string $phone): array
    {
        $normalized = self::normalize($phone);

        if ($normalized === '') {
            return [];
        }

        $variants = [$normalized, '+'.$normalized];

        if (str_starts_with($normalized, '62')) {
            $local = '0'.substr($normalized, 2);
            $variants[] = $local;
            $variants[] = substr($normalized, 2);
        }

        return array_values(array_unique($variants));
    }
}
