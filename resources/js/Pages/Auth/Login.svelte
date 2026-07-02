<script>
    import {
        ArrowLeft,
        AtSign,
        CheckCircle2,
        Globe2,
        Mail,
        MessageCircle,
        Phone,
        ShieldCheck,
    } from '@lucide/svelte';
    import BrandLogo from '@/Components/BrandLogo.svelte';

    export let contact = {
        site: 'https://browatt.com/',
        email: 'info@browatt.com',
        instagram: 'browatt.co',
    };

    let mobile = '';
    let code = '';
    let step = 'mobile';
    let isSubmitting = false;
    let normalizedMobile = '';
    let normalizedCode = '';
    let canRequestCode = false;
    let canVerifyCode = false;

    const toEnglishDigits = value =>
        String(value).replace(/[\u06F0-\u06F9\u0660-\u0669]/g, digit =>
            String(digit.charCodeAt(0) & 0xf),
        );
    const normalizeMobile = value => toEnglishDigits(value).replace(/[^\d+]/g, '');
    const normalizeCode = value => toEnglishDigits(value).replace(/\D/g, '');

    $: normalizedMobile = normalizeMobile(mobile);
    $: normalizedCode = normalizeCode(code);
    $: canRequestCode = normalizedMobile.length >= 11;
    $: canVerifyCode = normalizedCode.length >= 5;

    function handleMobileInput(event) {
        mobile = normalizeMobile(event.currentTarget.value).slice(0, 11);
    }

    function handleCodeInput(event) {
        code = normalizeCode(event.currentTarget.value).slice(0, 6);
    }

    function requestCode() {
        if (!canRequestCode) {
            return;
        }

        isSubmitting = true;

        window.setTimeout(() => {
            isSubmitting = false;
            step = 'code';
        }, 520);
    }

    function verifyCode() {
        if (!canVerifyCode) {
            return;
        }

        isSubmitting = true;

        window.setTimeout(() => {
            isSubmitting = false;
        }, 520);
    }
</script>

<svelte:head>
    <title>ورود به خدمات برووات</title>
</svelte:head>

<main class="min-h-screen overflow-hidden bg-[#f4f8fb] text-slate-950">
    <section class="grid min-h-screen lg:grid-cols-[1.04fr_0.96fr]">
        <aside class="relative hidden overflow-hidden bg-[#17275d] text-white lg:block">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_22%,rgba(27,167,224,0.34),transparent_30%),radial-gradient(circle_at_82%_72%,rgba(236,34,40,0.28),transparent_28%)]"></div>
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
                    <p class="mt-6 max-w-lg text-lg leading-9 text-white/74">
                        با ورود از طریق شماره موبایل، دسترسی مشتری به گارانتی‌ها و درخواست‌های خدماتی بدون رمز عبور انجام می‌شود.
                    </p>
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
                    <div class="mb-8 text-center lg:text-right">
                        <BrandLogo className="mx-auto mb-8 hidden w-48 lg:mx-0 lg:block" />
                        <p class="mb-3 inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-sm font-bold text-sky-700">
                            <MessageCircle size={16} />
                            ورود بدون رمز عبور
                        </p>
                        <h2 class="text-3xl font-black tracking-normal text-slate-950 sm:text-4xl">
                            ورود به حساب کاربری
                        </h2>
                        <p class="mt-3 text-base leading-8 text-slate-600">
                            شماره موبایل خود را وارد کنید تا کد تایید برای شما ارسال شود.
                        </p>
                    </div>

                    <div class="rounded-[2rem] border border-white/80 bg-white/88 p-4 shadow-[0_24px_80px_rgba(23,39,93,0.14)] backdrop-blur sm:p-6">
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
                                on:click={() => canRequestCode && (step = 'code')}
                            >
                                کد تایید
                            </button>
                        </div>

                        {#if step === 'mobile'}
                            <form class="space-y-5" on:submit|preventDefault={requestCode}>
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
                                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-lg font-bold tracking-[0.08em] text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                            placeholder="09123456789"
                                        />
                                    </span>
                                </label>

                                <button
                                    type="submit"
                                    disabled={!canRequestCode || isSubmitting}
                                    class="group inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#17275d] px-5 text-base font-black text-white shadow-lg shadow-[#17275d]/20 transition enabled:hover:-translate-y-0.5 enabled:hover:bg-[#1b3374] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                >
                                    {isSubmitting ? 'در حال ارسال...' : 'دریافت کد تایید'}
                                    <ArrowLeft class="transition group-enabled:group-hover:-translate-x-1" size={20} />
                                </button>
                            </form>
                        {:else}
                            <form class="space-y-5" on:submit|preventDefault={verifyCode}>
                                <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm leading-7 text-sky-800">
                                    کد تایید به شماره <span dir="ltr" class="font-black">{normalizedMobile || '09123456789'}</span> ارسال شد.
                                </div>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-bold text-slate-700">کد تایید پیامک شده</span>
                                    <input
                                        value={code}
                                        on:input={handleCodeInput}
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        dir="ltr"
                                        maxlength="6"
                                        class="h-16 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 text-center text-3xl font-black tracking-[0.34em] text-slate-950 outline-none transition placeholder:text-slate-300 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                                        placeholder="------"
                                    />
                                </label>

                                <button
                                    type="submit"
                                    disabled={!canVerifyCode || isSubmitting}
                                    class="group inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#ec2228] px-5 text-base font-black text-white shadow-lg shadow-red-500/18 transition enabled:hover:-translate-y-0.5 enabled:hover:bg-[#d61e24] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                >
                                    {isSubmitting ? 'در حال بررسی...' : 'ورود به پنل'}
                                    <CheckCircle2 size={20} />
                                </button>

                                <button
                                    type="button"
                                    class="h-11 w-full rounded-xl text-sm font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                                    on:click={() => (step = 'mobile')}
                                >
                                    اصلاح شماره موبایل
                                </button>
                            </form>
                        {/if}
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 text-sm text-slate-500 sm:grid-cols-2">
                        <a href={`mailto:${contact.email}`} class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/70 px-4 py-3 transition hover:bg-white hover:text-slate-800">
                            <Mail size={16} />
                            {contact.email}
                        </a>
                        <a href={`https://instagram.com/${contact.instagram}`} class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/70 px-4 py-3 transition hover:bg-white hover:text-slate-800">
                            <AtSign size={16} />
                            {contact.instagram}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </section>
</main>

<style>
    main::before {
        content: '';
        position: fixed;
        inset: auto -8rem -10rem auto;
        width: 22rem;
        height: 22rem;
        border-radius: 999px;
        background: color-mix(in srgb, #1ba7e0 18%, transparent);
        filter: blur(2px);
        pointer-events: none;
        animation: soft-float 12s ease-in-out infinite;
    }

    @keyframes soft-float {
        0%,
        100% {
            transform: translate3d(0, 0, 0);
        }

        50% {
            transform: translate3d(-1.5rem, -1rem, 0);
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
