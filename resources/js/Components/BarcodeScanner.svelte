<script>
    import { onDestroy, tick } from 'svelte';
    import { Camera, Flashlight, FlashlightOff, LoaderCircle, ScanLine, X } from '@lucide/svelte';

    const zxingReaderWasmUrl = '/vendor/zxing/zxing_reader.wasm';

    export let open = false;
    export let onScan = () => {};

    let videoElement;
    let detector = null;
    let stream = null;
    let scanning = false;
    let starting = false;
    let scanTimer = null;
    let loading = false;
    let errorMessage = '';
    let torchSupported = false;
    let torchEnabled = false;

    async function createDetector() {
        if (detector) {
            return detector;
        }

        let Detector = globalThis.BarcodeDetector;
        let supportedFormats = [];

        if (Detector?.getSupportedFormats) {
            try {
                supportedFormats = await Detector.getSupportedFormats();
            } catch {
                supportedFormats = [];
            }
        }

        if (!Detector || !supportedFormats.includes('code_128')) {
            const barcodeDetector = await import('barcode-detector/ponyfill');

            barcodeDetector.prepareZXingModule({
                overrides: {
                    locateFile: path => (path.endsWith('.wasm') ? zxingReaderWasmUrl : path),
                },
            });

            Detector = barcodeDetector.BarcodeDetector;
        }

        detector = new Detector({ formats: ['code_128'] });

        return detector;
    }

    async function startScanner() {
        if (starting || scanning) {
            return;
        }

        errorMessage = '';
        loading = true;
        starting = true;

        try {
            if (!navigator.mediaDevices?.getUserMedia) {
                throw new Error('مرورگر این دستگاه دسترسی مستقیم به دوربین را پشتیبانی نمی‌کند.');
            }

            if (!window.isSecureContext) {
                throw new Error('برای استفاده از دوربین، صفحه باید با HTTPS باز شود. روی موبایل، نسخه HTTPS آدرس پروژه را باز کنید.');
            }

            await tick();
            const activeDetector = await createDetector();

            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            });

            videoElement.srcObject = stream;
            videoElement.setAttribute('playsinline', 'true');
            await videoElement.play();

            const [track] = stream.getVideoTracks();
            const capabilities = track?.getCapabilities?.() ?? {};
            torchSupported = Boolean(capabilities.torch);

            scanning = true;
            scheduleScan(activeDetector);
        } catch (error) {
            errorMessage = error?.message || 'دوربین در دسترس نیست یا اجازه دسترسی داده نشده است.';
            stopScanner();
        } finally {
            loading = false;
            starting = false;
        }
    }

    function scheduleScan(activeDetector) {
        clearTimeout(scanTimer);

        scanTimer = setTimeout(async () => {
            if (!scanning || !videoElement) {
                return;
            }

            try {
                const results = await activeDetector.detect(videoElement);
                const code = results.find(result => result.format === 'code_128')?.rawValue?.trim();

                if (code) {
                    navigator.vibrate?.(60);
                    onScan(code);
                    closeScanner();
                    return;
                }
            } catch {
                // Some frames fail while autofocus is settling. Keep scanning the next frame.
            }

            scheduleScan(activeDetector);
        }, 140);
    }

    async function toggleTorch() {
        const [track] = stream?.getVideoTracks?.() ?? [];

        if (!track || !torchSupported) {
            return;
        }

        try {
            await track.applyConstraints({ advanced: [{ torch: !torchEnabled }] });
            torchEnabled = !torchEnabled;
        } catch {
            torchSupported = false;
        }
    }

    function stopScanner() {
        scanning = false;
        torchEnabled = false;
        torchSupported = false;
        clearTimeout(scanTimer);
        scanTimer = null;

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        if (videoElement) {
            videoElement.srcObject = null;
        }
    }

    function closeScanner() {
        open = false;
        stopScanner();
    }

    $: if (open) {
        startScanner();
    } else {
        stopScanner();
    }

    onDestroy(stopScanner);
</script>

{#if open}
    <div class="fixed inset-0 z-[80] flex items-end justify-center bg-slate-950/72 p-0 backdrop-blur-sm sm:items-center sm:p-4" role="dialog" aria-modal="true">
        <div class="flex h-[min(100dvh,44rem)] w-full flex-col overflow-hidden rounded-t-3xl bg-slate-950 text-white shadow-2xl sm:max-w-md sm:rounded-3xl">
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-4">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <ScanLine size={21} />
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-base font-black">اسکن بارکد</div>
                        <div class="mt-0.5 text-xs font-bold text-white/56">بارکد Code 128 روی محصول را داخل کادر بگیرید</div>
                    </div>
                </div>

                <button type="button" class="inline-flex size-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white transition active:scale-95" aria-label="بستن اسکنر" onclick={closeScanner}>
                    <X size={20} />
                </button>
            </div>

            <div class="relative min-h-0 flex-1 bg-black">
                <video bind:this={videoElement} class="h-full w-full object-cover" muted></video>

                <div class="pointer-events-none absolute inset-0 flex items-center justify-center p-8">
                    <div class="h-28 w-full max-w-sm rounded-3xl border-2 border-white shadow-[0_0_0_999px_rgba(2,6,23,0.42)]">
                        <div class="mt-1 h-0.5 w-full bg-[#ec2228] shadow-[0_0_18px_rgba(236,34,40,0.9)]"></div>
                    </div>
                </div>

                {#if loading}
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-950/76 text-white">
                        <LoaderCircle class="animate-spin" size={32} />
                        <div class="text-sm font-black">در حال آماده‌سازی دوربین</div>
                    </div>
                {/if}

                {#if errorMessage}
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-slate-950 px-6 text-center">
                        <Camera class="text-white/70" size={42} />
                        <div class="text-base font-black leading-8">{errorMessage}</div>
                    </div>
                {/if}
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-white/10 px-4 py-4">
                <div class="text-xs font-bold leading-6 text-white/60">بارکد را افقی و در نور کافی روبه‌روی دوربین نگه دارید.</div>

                {#if torchSupported}
                    <button type="button" class="inline-flex h-11 shrink-0 items-center gap-2 rounded-2xl bg-white px-4 text-sm font-black text-slate-950 transition active:scale-95" onclick={toggleTorch}>
                        {#if torchEnabled}
                            <FlashlightOff size={18} />
                        {:else}
                            <Flashlight size={18} />
                        {/if}
                        چراغ
                    </button>
                {/if}
            </div>
        </div>
    </div>
{/if}
