<div
    x-data="installBanner()"
    x-show="mode"
    x-cloak
    @pwa-installable.window="onInstallable()"
    @appinstalled.window="mode = null"
    class="bg-white border-b border-gray-200"
>
    <div class="max-w-3xl mx-auto px-4 py-3 flex items-start gap-3">
        <img src="{{ asset('icons/icon-192.png') }}" alt="" class="w-10 h-10 rounded-lg shrink-0">

        <div class="flex-1 min-w-0 text-sm">
            <p class="font-medium">{{ __('app.pwa_install_title') }}</p>

            <p x-show="mode === 'prompt'" class="text-gray-600">{{ __('app.pwa_install_text') }}</p>

            <p x-show="mode === 'ios'" class="text-gray-600">
                {{ __('app.pwa_ios_step_share') }}
                <svg class="inline w-4 h-4 -mt-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0-12L8 7m4-4l4 4M6 11H5a1 1 0 00-1 1v8a1 1 0 001 1h14a1 1 0 001-1v-8a1 1 0 00-1-1h-1" />
                </svg>
                {{ __('app.pwa_ios_step_add') }}
            </p>

            <button
                x-show="mode === 'prompt'"
                type="button"
                @click="install()"
                class="mt-2 inline-flex items-center justify-center rounded-lg bg-gray-900 text-white font-medium px-4 py-2"
            >
                {{ __('app.pwa_install_button') }}
            </button>
        </div>

        <button
            type="button"
            @click="dismiss()"
            class="shrink-0 -mr-2 -mt-1 p-2 text-gray-400 hover:text-gray-700"
            aria-label="{{ __('app.pwa_dismiss') }}"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<script>
    function installBanner() {
        const storageKey = 'pwa-install-dismissed';

        const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        const isDismissed = () => {
            try {
                return window.localStorage.getItem(storageKey) === '1';
            } catch (error) {
                return false;
            }
        };

        const isIosSafari = () => {
            const ua = window.navigator.userAgent;
            const isIos = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            return isIos && /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);
        };

        return {
            mode: null,

            init() {
                if (isStandalone() || isDismissed()) {
                    return;
                }

                if (window.deferredInstallPrompt) {
                    this.mode = 'prompt';
                } else if (isIosSafari()) {
                    this.mode = 'ios';
                }
            },

            onInstallable() {
                if (!isStandalone() && !isDismissed()) {
                    this.mode = 'prompt';
                }
            },

            async install() {
                const prompt = window.deferredInstallPrompt;

                if (!prompt) {
                    return;
                }

                prompt.prompt();
                await prompt.userChoice;
                window.deferredInstallPrompt = null;
                this.mode = null;
            },

            dismiss() {
                this.mode = null;

                try {
                    window.localStorage.setItem(storageKey, '1');
                } catch (error) {
                    // Storage unavailable (e.g. private mode): the banner just returns next visit.
                }
            },
        };
    }
</script>
