import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { Toaster } from 'sonner';
import { initializeTheme, useAppearance } from '@hooks/use-appearance';
import { useFlashToast } from '@hooks/use-flash-toast';
import '@styles/app.css';
import '@lib/i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        const Root = () => {
            const { resolvedAppearance } = useAppearance();
            useFlashToast();

            return (
                <>
                    <App {...props} />
                    <Toaster
                        richColors
                        position="top-right"
                        theme={resolvedAppearance}
                    />
                </>
            );
        };

        root.render(
            <StrictMode>
                <Root />
            </StrictMode>,
        );
    },
    progress: {
        color: '#FF6900',
    },
});

// This will set light / dark mode on load...
initializeTheme();
