<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Illuminate\Translation\FileLoader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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

        $localeDirectoryPath = "{$baseJsonPath}/{$locale}";

        if (! is_dir(filename: $localeDirectoryPath)) {
            return [];
        }

        $groupedTranslations = [];

        $directoryIterator = new RecursiveDirectoryIterator(
            directory: $localeDirectoryPath,
            flags: RecursiveDirectoryIterator::SKIP_DOTS,
        );

        $translationFiles = new RecursiveIteratorIterator(iterator: $directoryIterator);

        foreach ($translationFiles as $translationFile) {
            if (! $translationFile instanceof SplFileInfo || ! $translationFile->isFile()) {
                continue;
            }

            if ($translationFile->getExtension() !== 'json') {
                continue;
            }

            $translationFilePath = $translationFile->getPathname();
            $relativeTranslationFilePath = mb_substr(
                string: $translationFilePath,
                start: mb_strlen($localeDirectoryPath) + 1,
            );

            if ($relativeTranslationFilePath === false || $relativeTranslationFilePath === '') {
                continue;
            }

            $normalizedTranslationFilePath = str_replace(
                search: '\\',
                replace: '/',
                subject: $relativeTranslationFilePath,
            );

            $translationGroup = str_replace(
                search: '/',
                replace: '.',
                subject: preg_replace(
                    pattern: '/\.json$/',
                    replacement: '',
                    subject: $normalizedTranslationFilePath,
                ) ?? '',
            );

            if ($translationGroup === '') {
                continue;
            }

            $rawJsonContents = file_get_contents(filename: $translationFilePath);

            if ($rawJsonContents === false) {
                continue;
            }

            $decodedTranslations = json_decode(
                json: $rawJsonContents,
                associative: true,
            );

            if (! is_array($decodedTranslations)) {
                continue;
            }

            $groupedTranslations[$translationGroup] = $decodedTranslations;
        }

        return $this->flattenTranslations($groupedTranslations);
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
