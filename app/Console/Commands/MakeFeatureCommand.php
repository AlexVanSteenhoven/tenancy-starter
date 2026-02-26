<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:feature {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffolds a new feature and all required files and directories';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $featureName = (string) $this->argument('name');
        $normalizedName = str_replace('\\', '/', $featureName);
        $segments = array_values(array_filter(explode('/', $normalizedName)));

        if ($segments === []) {
            $this->error('Feature name is required.');

            return self::FAILURE;
        }

        $className = array_pop($segments);

        if (! is_string($className) || $className === '') {
            $this->error('Invalid feature name.');

            return self::FAILURE;
        }

        $baseName = str_ends_with($className, 'Controller')
            ? (string) Str::beforeLast($className, 'Controller')
            : $className;

        $controllerDirectory = app_path('Http/Controllers'.($segments !== [] ? '/'.implode('/', $segments) : ''));
        $controllerNamespace = 'App\\Http\\Controllers'.($segments !== [] ? '\\'.implode('\\', $segments) : '');
        $controllerPath = $controllerDirectory.'/'.$className.'.php';
        $isShowFeature = preg_match('/^(Show|List|Index)/', $baseName) === 1;

        $createdFiles = [];

        File::ensureDirectoryExists($controllerDirectory);

        if ($isShowFeature) {
            $pageSegments = array_map(
                static fn (string $segment): string => Str::kebab($segment),
                [...$segments, $baseName],
            );
            $pageComponent = implode('/', $pageSegments);
            $pagePath = resource_path('js/pages/'.$pageComponent.'.tsx');
            $pageDirectory = dirname($pagePath);

            File::ensureDirectoryExists($pageDirectory);

            File::put(
                $controllerPath,
                $this->showControllerStub(
                    namespace: $controllerNamespace,
                    className: $className,
                    pageComponent: $pageComponent,
                ),
            );

            File::put(
                $pagePath,
                $this->reactPageStub(
                    componentName: $baseName,
                    translationBaseKey: implode('.', $pageSegments),
                ),
            );

            $createdFiles[] = $controllerPath;
            $createdFiles[] = $pagePath;
        } else {
            $requestClass = $baseName.'Request';
            $actionClass = $baseName.'Action';
            $requestName = ($segments !== [] ? implode('/', $segments).'/' : '').$requestClass;
            $actionName = ($segments !== [] ? implode('/', $segments).'/' : '').$actionClass;
            $requestNamespace = 'App\\Http\\Requests'.($segments !== [] ? '\\'.implode('\\', $segments) : '');
            $actionNamespace = 'App\\Actions'.($segments !== [] ? '\\'.implode('\\', $segments) : '');

            $requestExitCode = $this->call('make:request', ['name' => $requestName]);

            if ($requestExitCode !== self::SUCCESS) {
                $this->error('Could not create request class.');

                return self::FAILURE;
            }

            $actionExitCode = $this->call('make:action', ['name' => $actionName]);

            if ($actionExitCode !== self::SUCCESS) {
                $this->error('Could not create action class.');

                return self::FAILURE;
            }

            File::put(
                $controllerPath,
                $this->mutationControllerStub(
                    namespace: $controllerNamespace,
                    className: $className,
                    requestNamespace: $requestNamespace,
                    requestClass: $requestClass,
                    actionNamespace: $actionNamespace,
                    actionClass: $actionClass,
                ),
            );

            $createdFiles[] = $controllerPath;
            $createdFiles[] = app_path('Http/Requests/'.$requestName.'.php');
            $createdFiles[] = app_path('Actions/'.$actionName.'.php');
        }

        $this->info('Scaffolded files:');

        foreach ($createdFiles as $createdFile) {
            $this->line('- '.$createdFile);
        }

        $this->info('Feature scaffolding complete.');

        return self::SUCCESS;
    }

    private function showControllerStub(string $namespace, string $className, string $pageComponent): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class {$className} extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('{$pageComponent}');
    }
}
PHP;
    }

    private function mutationControllerStub(
        string $namespace,
        string $className,
        string $requestNamespace,
        string $requestClass,
        string $actionNamespace,
        string $actionClass
    ): string {
        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use App\Http\Controllers\Controller;
use {$actionNamespace}\\{$actionClass};
use {$requestNamespace}\\{$requestClass};
use Illuminate\Http\RedirectResponse;

final class {$className} extends Controller
{
    public function __invoke({$requestClass} \$request, {$actionClass} \$action): RedirectResponse
    {
        \$action->handle(\$request);

        return back();
    }
}
PHP;
    }

    private function reactPageStub(string $componentName, string $translationBaseKey): string
    {
        return <<<TSX
import '@lib/i18n';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function {$componentName}() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('{$translationBaseKey}.meta.title')} />
        </>
    );
}
TSX;
    }
}
