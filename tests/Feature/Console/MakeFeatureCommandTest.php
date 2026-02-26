<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function (): void {
    File::deleteDirectory(app_path('Http/Controllers/MakeFeatureCommandTest'));
    File::deleteDirectory(app_path('Http/Requests/MakeFeatureCommandTest'));
    File::deleteDirectory(app_path('Actions/MakeFeatureCommandTest'));
    File::deleteDirectory(resource_path('js/pages/make-feature-command-test'));
});

test('it scaffolds a show feature with a react page', function (): void {
    $this->artisan('make:feature MakeFeatureCommandTest/ShowUsersController')
        ->assertExitCode(0);

    $controllerPath = app_path('Http/Controllers/MakeFeatureCommandTest/ShowUsersController.php');
    $pagePath = resource_path('js/pages/make-feature-command-test/show-users.tsx');

    expect(File::exists($controllerPath))->toBeTrue();
    expect(File::exists($pagePath))->toBeTrue();

    $controllerContents = File::get($controllerPath);
    $pageContents = File::get($pagePath);

    expect($controllerContents)
        ->toContain('namespace App\\Http\\Controllers\\MakeFeatureCommandTest;')
        ->toContain("return Inertia::render('make-feature-command-test/show-users');");

    expect($pageContents)
        ->toContain('export default function ShowUsers()')
        ->toContain("Head title={t('make-feature-command-test.show-users.meta.title')}");
});

test('it scaffolds a mutation feature with a request and action', function (): void {
    $this->artisan('make:feature MakeFeatureCommandTest/StoreUserController')
        ->assertExitCode(0);

    $controllerPath = app_path('Http/Controllers/MakeFeatureCommandTest/StoreUserController.php');
    $requestPath = app_path('Http/Requests/MakeFeatureCommandTest/StoreUserRequest.php');
    $actionPath = app_path('Actions/MakeFeatureCommandTest/StoreUserAction.php');

    expect(File::exists($controllerPath))->toBeTrue();
    expect(File::exists($requestPath))->toBeTrue();
    expect(File::exists($actionPath))->toBeTrue();

    $controllerContents = File::get($controllerPath);

    expect($controllerContents)
        ->toContain('use App\\Actions\\MakeFeatureCommandTest\\StoreUserAction;')
        ->toContain('use App\\Http\\Requests\\MakeFeatureCommandTest\\StoreUserRequest;')
        ->toContain('public function __invoke(StoreUserRequest $request, StoreUserAction $action): RedirectResponse');
});
