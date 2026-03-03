import { router } from '@inertiajs/react';
import { gooeyToast } from 'goey-toast';
import { useEffect, useRef } from 'react';

type FlashData = {
    status?: string | null;
    statusDescription?: string | null;
};

export function useFlashToast(): void {
    const lastToastKeyRef = useRef<string | null>(null);

    useEffect(() => {
        const showToast = (flash: FlashData | undefined): void => {
            if (!flash?.status) {
                return;
            }

            const toastKey = `${flash.status}|${flash.statusDescription ?? ''}`;

            if (toastKey === lastToastKeyRef.current) {
                return;
            }

            lastToastKeyRef.current = toastKey;

            gooeyToast.success(flash.status, {
                description: flash.statusDescription ?? undefined,
            });
        };

        const stopListening = router.on('success', (event) => {
            const flash = (event.detail.page.props as { flash?: FlashData }).flash;
            showToast(flash);
        });

        return () => {
            stopListening();
        };
    }, []);
}
