<?php

declare(strict_types=1);

use App\Services\Translation\TranslationLoader;
use Illuminate\Filesystem\Filesystem;

test('translation loader loads flat and nested JSON translation files', function (): void {
    $filesystem = new Filesystem();
    $translationsPath = sys_get_temp_dir().'/translations-'.uniqid(prefix: '', more_entropy: true);
    $englishTranslationsPath = "{$translationsPath}/en";

    $filesystem->ensureDirectoryExists(path: "{$englishTranslationsPath}/pages/settings");

    $filesystem->put(
        path: "{$englishTranslationsPath}/users.json",
        contents: json_encode([
            'actions' => [
                'invite' => 'Invite users',
            ],
        ], JSON_THROW_ON_ERROR),
    );

    $filesystem->put(
        path: "{$englishTranslationsPath}/pages/dashboard.json",
        contents: json_encode([
            'meta' => [
                'title' => 'Dashboard',
            ],
        ], JSON_THROW_ON_ERROR),
    );

    $filesystem->put(
        path: "{$englishTranslationsPath}/pages/settings/profile.json",
        contents: json_encode([
            'meta' => [
                'title' => 'Profile settings',
            ],
        ], JSON_THROW_ON_ERROR),
    );

    try {
        $loader = new TranslationLoader(
            files: $filesystem,
            path: $translationsPath,
        );

        $translations = $loader->load(locale: 'en', group: '*', namespace: '*');

        expect($translations)
            ->toHaveKey('users.actions.invite', 'Invite users')
            ->toHaveKey('pages.dashboard.meta.title', 'Dashboard')
            ->toHaveKey('pages.settings.profile.meta.title', 'Profile settings');
    } finally {
        $filesystem->deleteDirectory(directory: $translationsPath);
    }
});
