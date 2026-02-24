<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Illuminate\Translation\FileLoader;

/**
 * Custom JSON loader that supports nested translation keys by flattening them
 * into Laravel's dot notation.
 */
final class TranslationLoader extends FileLoader
{
    /**
     * Load translations from the base JSON file for a locale.
     *
     * @param  mixed  $locale
     * @return array<string, mixed>
     */
    protected function loadJsonPaths($locale): array
    {
        $baseJsonPath = $this->getBaseJsonPath();

        if ($baseJsonPath === null) {
            return [];
        }

        $localeFilePath = "{$baseJsonPath}/{$locale}.json";

        if (! is_file(filename: $localeFilePath)) {
            return [];
        }

        $rawJsonContents = file_get_contents(filename: $localeFilePath);

        if ($rawJsonContents === false) {
            return [];
        }

        $decodedTranslations = json_decode(
            json: $rawJsonContents,
            associative: true,
        );

        if (! is_array($decodedTranslations)) {
            return [];
        }

        return $this->flattenTranslations($decodedTranslations);
    }

    /**
     * Get the first configured JSON path.
     */
    private function getBaseJsonPath(): ?string
    {
        return collect($this->paths)->first();
    }

    /**
     * Flatten nested translation arrays into dot-notation keys.
     *
     * @param  array<string|int, mixed>  $translations
     * @return array<string, mixed>
     */
    private function flattenTranslations(array $translations): array
    {
        $flattenedTranslations = [];

        $this->flattenIntoDotNotation(
            translations: $translations,
            flattenedTranslations: $flattenedTranslations,
            currentPrefix: '',
        );

        return $flattenedTranslations;
    }

    /**
     * Recursively flatten nested translation arrays.
     *
     * @param  array<string|int, mixed>  $translations
     * @param  array<string, mixed>  $flattenedTranslations
     */
    private function flattenIntoDotNotation(array $translations, array &$flattenedTranslations, string $currentPrefix): void
    {
        foreach ($translations as $key => $value) {
            $dotKey = $this->buildDotKey(currentPrefix: $currentPrefix, key: (string) $key);

            if (is_array($value)) {
                $this->flattenIntoDotNotation(
                    translations: $value,
                    flattenedTranslations: $flattenedTranslations,
                    currentPrefix: $dotKey,
                );

                continue;
            }

            $flattenedTranslations[$dotKey] = $value;
        }
    }

    private function buildDotKey(string $currentPrefix, string $key): string
    {
        if ($currentPrefix === '' || $currentPrefix === '0') {
            return $key;
        }

        return "{$currentPrefix}.{$key}";
    }
}
