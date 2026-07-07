<script>
    import { onDestroy, tick } from 'svelte';
    import {
        AlertCircle,
        ArrowLeft,
        CheckCircle2,
        Clock3,
        Globe2,
        Mail,
        Phone,
        RefreshCw,
        ShieldCheck,
    } from '@lucide/svelte';
    import BrandLogo from '@/Components/BrandLogo.svelte';
    import * as Alert from '@/Components/ui/alert';
    import * as InputOTP from '@/Components/ui/input-otp';

    export let contact = {
        site: 'https://browatt.com/',
        email: 'info@browatt.com',
        instagram: 'browatt.co',
    };

    export let otp = {
        resendSeconds: 60,
        codeLength: 6,
    };

    let mobile = '';
    let code = '';
    let step = 'mobile';
    let isRequesting = false;
    let isVerifying = false;
    let resendRemaining = 0;
    let expiresRemaining = 0;
    let hasRequestedCode = false;
    let notice = null;
    let resendTimer = null;
    let expiryTimer = null;
    let normalizedMobile = '';
    let normalizedCode = '';
    let codeLength = 6;
    let canRequestCode = false;
    let canVerifyCode = false;
    let otpInput = null;
    let lastSubmittedCode = '';

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    const toEnglishDigits = value =>
        String(value).replace(/[\u06F0-\u06F9\u0660-\u0669]/g, digit =>
            String(digit.charCodeAt(0) & 0xf),
        );
    const normalizeMobile = value => toEnglishDigits(value).replace(/[^\d+]/g, '');
    const normalizeCode = value => toEnglishDigits(value).replace(/\D/g, '');
    const formatSeconds = seconds => `۰:${String(Math.max(seconds, 0)).padStart(2, '0')}`;
    $: codeLength = Number(otp.codeLength ?? 6);
    $: normalizedMobile = normalizeMobile(mobile);
    $: normalizedCode = normalizeCode(code).slice(0, codeLength);
    $: canRequestCode = /^09\d{9}$/.test(normalizedMobile);
    $: canVerifyCode = normalizedCode.length === codeLength;
    $: isBusy = isRequesting || isVerifying;

    onDestroy(() => {
        clearTimer(resendTimer);
        clearTimer(expiryTimer);
    });

    function handleMobileInput(event) {
        mobile = normalizeMobile(event.currentTarget.value).slice(0, 11);
        notice = null;
    }

    async function requestCode() {
        if (!canRequestCode || isRequesting) {
            return;
        }

        isRequesting = true;
        notice = null;

        try {
            const { data } = await window.axios.post(routeUrl('login.otp.request', '/login/otp'), {
                mobile: normalizedMobile,
            });

            hasRequestedCode = true;
            code = '';
            step = 'code';
            startResendTimer(Number(data.resend_after ?? otp.resendSeconds ?? 60));
            startExpiryTimer(Number(data.expires_in ?? 120));
            focusOtpInput();
            notice = {
                type: 'success',
                text: data.message ?? 'کد تایید برای شماره موبایل شما ارسال شد.',
            };
        } catch (error) {
            handleApiError(error, 'ارسال کد تایید فعلا انجام نشد. چند دقیقه دیگر دوباره تلاش کنید.');
        } finally {
            isRequesting = false;
        }
    }

    async function resendCode() {
        if (!hasRequestedCode || resendRemaining > 0 || isRequesting) {
            return;
        }

        await requestCode();
    }

    async function verifyCode() {
        if (!canVerifyCode || isVerifying) {
            return;
        }

        isVerifying = true;
        lastSubmittedCode = normalizedCode;
        notice = null;

        try {
            const { data } = await window.axios.post(routeUrl('login.otp.verify', '/login/otp/verify'), {
                mobile: normalizedMobile,
                code: normalizedCode,
            });

            window.location.assign(data.redirect ?? '/dashboard');
        } catch (error) {
            handleApiError(error, 'کد تایید نادرست یا منقضی شده است.');
            lastSubmittedCode = '';
        } finally {
            isVerifying = false;
        }
    }

    async function handleCodeComplete(value = code) {
        code = normalizeCode(value || code).slice(0, codeLength);

        await tick();

        if (normalizedCode.length === codeLength && normalizedCode !== lastSubmittedCode && !isVerifying) {
            verifyCode();
        }
    }

    async function focusOtpInput() {
        await tick();

        window.setTimeout(() => {
            otpInput?.focus();
        }, 0);
    }

    function showCodeStep() {
        if (!hasRequestedCode) {
            return;
        }

        step = 'code';
        focusOtpInput();
    }

    function handleApiError(error, fallbackMessage) {
        const data = error?.response?.data ?? {};
        const status = error?.response?.status;
        let text = data.message ?? fallbackMessage;

        if (Number(data.retry_after) > 0) {
            startResendTimer(Number(data.retry_after));
        }

        if (status === 429 && !Number(data.retry_after)) {
            text = 'تعداد درخواست‌ها زیاد است. لطفا کمی بعد دوباره تلاش کنید.';
        }

        if (status === 419) {
            text = 'اعتبار صفحه تمام شده است. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.';
        }

        notice = {
            type: 'error',
            text,
        };
    }

    function startResendTimer(seconds) {
        clearTimer(resendTimer);
        resendRemaining = Math.max(0, seconds);

        if (resendRemaining === 0) {
            return;
        }

        resendTimer = window.setInterval(() => {
            resendRemaining -= 1;

            if (resendRemaining <= 0) {
                clearTimer(resendTimer);
                resendTimer = null;
                resendRemaining = 0;
            }
        }, 1000);
    }

    function startExpiryTimer(seconds) {
        clearTimer(expiryTimer);
        expiresRemaining = Math.max(0, seconds);

        if (expiresRemaining === 0) {
            return;
        }

        expiryTimer = window.setInterval(() => {
            expiresRemaining -= 1;

            if (expiresRemaining <= 0) {
                clearTimer(expiryTimer);
                expiryTimer = null;
                expiresRemaining = 0;
            }
        }, 1000);
    }

    function clearTimer(timer) {
        if (timer !== null) {
            window.clearInterval(timer);
        }
    }
</script>

<svelte:head>
    <title>ورود به خدمات برووات</title>
</svelte:head>

<main class="auth-shell relative min-h-screen overflow-hidden bg-[#f4f8fb] text-slate-950">
    <div class="ambient-lines" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="brand-motion" aria-hidden="true">
        <span></span>
        <span></span>
    </div>

    <section class="relative z-10 grid min-h-screen lg:grid-cols-[1.04fr_0.96fr]">
        <aside class="relative hidden overflow-hidden bg-[#17275d] text-white lg:block">
            <div class="panel-pattern absolute inset-0"></div>
            <div class="absolute -left-20 top-20 h-72 w-72 rounded-full border border-white/10"></div>
            <div class="absolute bottom-16 right-12 h-48 w-48 rounded-full border border-white/10"></div>

            <div class="relative flex h-full flex-col justify-between p-12 xl:p-16">
                <BrandLogo variant="white" className="w-44" />

                <div class="max-w-xl">
                    <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-white/85 backdrop-blur">
                        <ShieldCheck size={17} strokeWidth={2.2} />
                        خدمات پس از فروش رسمی برووات
                    </div>
                    <h1 class="text-4xl font-black leading-[1.45] xl:text-5xl">
                        فعال‌سازی گارانتی، پیگیری خدمات و پشتیبانی در یک حساب امن
                    </h1>
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm text-white/72">
                    <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur">
                        <span class="block text-2xl font-black text-white">۱</span>
                        ورود سریع
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur">
                        <span class="block text-2xl font-black text-white">۲</span>
                        تایید پیامکی
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur">
                        <span class="block text-2xl font-black text-white">۳</span>
                        گارانتی فعال
                    </div>
                </div>
            </div>
        </aside>

        <section class="relative flex min-h-screen flex-col px-5 py-6 sm:px-8 lg:px-12">
            <header class="flex items-center justify-between gap-4">
                <BrandLogo className="w-36 sm:w-40 lg:hidden" />
                <div class="hidden lg:block"></div>

                <a
                    href={contact.site}
                    class="inline-flex h-11 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-200 hover:text-sky-700"
                >
                    <Globe2 size={17} />
                    سایت برووات
                </a>
            </header>

            <div class="flex flex-1 items-center justify-center py-10">
                <div class="w-full max-w-[440px]">
                    <div class="mb-7 text-center lg:text-right">
                        <BrandLogo className="mx-auto mb-8 hidden w-48 lg:mx-0 lg:block" />
                        <h2 class="text-3xl font-black tracking-normal text-slate-950 sm:text-4xl">
                            ورود به حساب کاربری
                        </h2>
                        <p class="mt-3 text-base leading-8 text-slate-600">
                            شماره موبایل خود را وارد کنید تا کد تایید برای شما ارسال شود.
                        </p>
                    </div>

                    <div class="auth-card rounded-[2rem] border border-white/80 bg-white/88 p-4 shadow-[0_24px_80px_rgba(23,39,93,0.14)] backdrop-blur sm:p-6">
                        <div class="mb-5 flex rounded-2xl bg-slate-100 p-1 text-sm font-bold text-slate-500">
                            <button
                                type="button"
                                class={`h-11 flex-1 rounded-xl transition ${step === 'mobile' ? 'bg-white text-slate-950 shadow-sm' : ''}`}
                                on:click={() => (step = 'mobile')}
                            >
                                شماره موبایل
                            </button>
                            <button
                                type="button"
                                class={`h-11 flex-1 rounded-xl transition ${step === 'code' ? 'bg-white text-slate-950 shadow-sm' : ''}`}
                                disabled={!hasRequestedCode}
                                on:click={showCodeStep}
                            >
                                کد تایید
                            </button>
                        </div>

                        {#if notice}
                            <Alert.Root variant={notice.type === 'error' ? 'destructive' : 'default'} class="mb-5">
                                {#if notice.type === 'error'}
                                    <AlertCircle />
                                {:else}
                                    <CheckCircle2 />
                                {/if}
                                <Alert.Description>{notice.text}</Alert.Description>
                            </Alert.Root>
                        {/if}

                        {#if step === 'mobile'}
                            <form class="flex flex-col gap-5" on:submit|preventDefault={requestCode}>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-bold text-slate-700">شماره موبایل</span>
                                    <span class="relative block">
                                        <Phone class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
                                        <input
                                            value={mobile}
                                            on:input={handleMobileInput}
                                            inputmode="tel"
                                            autocomplete="tel"
                                            dir="ltr"
                                            aria-invalid={notice?.type === 'error'}
                                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-lg font-bold tracking-[0.08em] text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                            placeholder="09123456789"
                                        />
                                    </span>
                                </label>

                                <button
                                    type="submit"
                                    disabled={!canRequestCode || isRequesting}
                                    class="group inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#17275d] px-5 text-base font-black text-white shadow-lg shadow-[#17275d]/20 transition enabled:hover:-translate-y-0.5 enabled:hover:bg-[#1b3374] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                >
                                    {isRequesting ? 'در حال ارسال...' : 'دریافت کد تایید'}
                                    <ArrowLeft class="transition group-enabled:group-hover:-translate-x-1" size={20} />
                                </button>
                            </form>
                        {:else}
                            <form class="flex flex-col gap-5" on:submit|preventDefault={verifyCode}>
                                <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm leading-7 text-sky-800">
                                    کد تایید به شماره <span dir="ltr" class="font-black">{normalizedMobile}</span> ارسال شد.
                                    {#if expiresRemaining > 0}
                                        <span class="mt-1 flex items-center gap-1 text-xs font-bold text-sky-700">
                                            <Clock3 size={14} />
                                            اعتبار تقریبی کد: {formatSeconds(expiresRemaining)}
                                        </span>
                                    {/if}
                                </div>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-bold text-slate-700">کد تایید پیامک شده</span>
                                    <InputOTP.Root
                                        bind:value={code}
                                        bind:inputRef={otpInput}
                                        maxlength={codeLength}
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        inputId="login-otp-code"
                                        name="otp"
                                        dir="ltr"
                                        textalign="left"
                                        aria-invalid={notice?.type === 'error'}
                                        class="w-full justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 [direction:ltr] transition focus-within:border-sky-400 focus-within:bg-white focus-within:ring-4 focus-within:ring-sky-100"
                                        oninput={() => {
                                            notice = null;
                                            lastSubmittedCode = '';
                                        }}
                                        onComplete={handleCodeComplete}
                                        pasteTransformer={normalizeCode}
                                    >
                                        {#snippet children({ cells })}
                                            <InputOTP.Group class="[direction:ltr]">
                                                {#each cells as cell (cell)}
                                                    <InputOTP.Slot
                                                        {cell}
                                                        class="size-11 rounded-xl border border-slate-200 bg-white text-2xl font-black text-slate-950 shadow-sm sm:size-12"
                                                    />
                                                {/each}
                                            </InputOTP.Group>
                                        {/snippet}
                                    </InputOTP.Root>
                                </label>

                                <button
                                    type="submit"
                                    disabled={!canVerifyCode || isVerifying}
                                    class="group inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#ec2228] px-5 text-base font-black text-white shadow-lg shadow-red-500/18 transition enabled:hover:-translate-y-0.5 enabled:hover:bg-[#d61e24] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                >
                                    {isVerifying ? 'در حال بررسی...' : 'ورود به پنل'}
                                    <CheckCircle2 size={20} />
                                </button>

                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        disabled={resendRemaining > 0 || isBusy}
                                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl text-sm font-bold text-sky-700 transition enabled:hover:bg-sky-50 disabled:cursor-not-allowed disabled:text-slate-400"
                                        on:click={resendCode}
                                    >
                                        <RefreshCw class={isRequesting ? 'animate-spin' : ''} size={16} />
                                        {#if resendRemaining > 0}
                                            ارسال مجدد تا {resendRemaining} ثانیه
                                        {:else}
                                            ارسال مجدد کد
                                        {/if}
                                    </button>

                                    <button
                                        type="button"
                                        class="h-11 rounded-xl text-sm font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                                        on:click={() => {
                                            step = 'mobile';
                                            notice = null;
                                        }}
                                    >
                                        اصلاح شماره موبایل
                                    </button>
                                </div>
                            </form>
                        {/if}
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 text-sm text-slate-500 sm:grid-cols-2">
                        <a href={`mailto:${contact.email}`} class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/70 px-4 py-3 transition hover:bg-white hover:text-slate-800">
                            <Mail size={16} />
                            {contact.email}
                        </a>
                        <a href={`https://instagram.com/${contact.instagram}`} class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/70 px-4 py-3 transition hover:bg-white hover:text-slate-800">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect width="15.5" height="15.5" x="4.25" y="4.25" rx="4.25" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="12" cy="12" r="3.35" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="16.55" cy="7.45" r="1" fill="currentColor" />
                            </svg>
                            {contact.instagram}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </section>
</main>

<style>
    .auth-shell::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            linear-gradient(120deg, transparent 0 55%, rgba(27, 167, 224, 0.08) 55% 56%, transparent 56%),
            linear-gradient(160deg, transparent 0 64%, rgba(236, 34, 40, 0.06) 64% 65%, transparent 65%);
        pointer-events: none;
    }

    .ambient-lines {
        position: fixed;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .ambient-lines span {
        position: absolute;
        width: 42rem;
        height: 18rem;
        border: 1px solid rgba(27, 167, 224, 0.2);
        border-right-color: transparent;
        border-bottom-color: rgba(236, 34, 40, 0.14);
        border-radius: 999px;
        transform: rotate(-18deg);
        animation: airflow 15s ease-in-out infinite;
    }

    .ambient-lines span:nth-child(1) {
        right: -16rem;
        top: 12%;
    }

    .ambient-lines span:nth-child(2) {
        right: 12%;
        bottom: -10rem;
        animation-delay: -5s;
        animation-duration: 18s;
    }

    .ambient-lines span:nth-child(3) {
        left: -18rem;
        top: 46%;
        animation-delay: -9s;
        animation-duration: 21s;
    }

    .brand-motion span {
        position: fixed;
        width: 18rem;
        height: 18rem;
        border-radius: 999px;
        pointer-events: none;
        mix-blend-mode: multiply;
        filter: blur(10px);
        opacity: 0.28;
        animation: brand-drift 13s ease-in-out infinite;
    }

    .brand-motion span:first-child {
        right: 10%;
        top: 10%;
        background: radial-gradient(circle, rgba(27, 167, 224, 0.45), transparent 68%);
    }

    .brand-motion span:last-child {
        left: 4%;
        bottom: 8%;
        background: radial-gradient(circle, rgba(236, 34, 40, 0.26), transparent 70%);
        animation-delay: -6s;
        animation-duration: 16s;
    }

    .panel-pattern {
        background:
            radial-gradient(circle at 20% 22%, rgba(27, 167, 224, 0.34), transparent 30%),
            radial-gradient(circle at 82% 72%, rgba(236, 34, 40, 0.28), transparent 28%),
            repeating-linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 18px);
    }

    .auth-card {
        animation: card-enter 480ms ease-out both;
    }

    @keyframes airflow {
        0%,
        100% {
            transform: translate3d(0, 0, 0) rotate(-18deg);
            opacity: 0.72;
        }

        50% {
            transform: translate3d(-1.25rem, -0.75rem, 0) rotate(-16deg);
            opacity: 1;
        }
    }

    @keyframes card-enter {
        from {
            transform: translateY(0.75rem);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes brand-drift {
        0%,
        100% {
            transform: translate3d(0, 0, 0) scale(1);
        }

        50% {
            transform: translate3d(1.25rem, -1rem, 0) scale(1.08);
        }
    }

    @media (max-width: 640px) {
        .ambient-lines span {
            width: 24rem;
            height: 12rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.001ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: 0.001ms !important;
        }
    }
</style>
