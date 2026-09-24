<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#111827">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="CrossFit Grozny">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<script>
    {{-- Captured here, before Alpine loads, because the browser may fire the event early. --}}
    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        window.deferredInstallPrompt = event;
        window.dispatchEvent(new CustomEvent('pwa-installable'));
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}');
        });
    }
</script>
