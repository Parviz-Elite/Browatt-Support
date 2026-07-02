<script>
    import BrandLogo from '@/Components/BrandLogo.svelte';
</script>

<svelte:head>
    <title>داشبورد خدمات برووات</title>
</svelte:head>

<main class="min-h-screen bg-[#f4f8fb] px-5 py-6 text-slate-950">
    <section class="mx-auto flex min-h-[calc(100vh-3rem)] max-w-5xl flex-col">
        <header class="flex items-center justify-between gap-4">
            <BrandLogo className="w-36" />
            <div class="rounded-full bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm">
                پنل خدمات پس از فروش
            </div>
        </header>

        <div class="flex flex-1 items-center justify-center py-12">
            <div class="w-full max-w-xl rounded-[2rem] border border-white/80 bg-white/90 p-6 text-center shadow-[0_24px_80px_rgba(23,39,93,0.12)] sm:p-8">
                <p class="mb-3 inline-flex rounded-full bg-sky-50 px-3 py-1.5 text-sm font-bold text-sky-700">
                    ورود موفق
                </p>
                <h1 class="text-3xl font-black leading-10 text-slate-950">
                    به سامانه خدمات برووات خوش آمدید
                </h1>
                <p class="mt-4 leading-8 text-slate-600">
                    بخش فعال‌سازی گارانتی در مرحله بعدی این پنل تکمیل می‌شود.
                </p>
            </div>
        </div>
    </section>
</main>
