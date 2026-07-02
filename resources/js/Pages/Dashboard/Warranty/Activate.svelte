<script>
    import { motion } from '@humanspeak/svelte-motion';
    import {
        ArrowLeft,
        BadgeCheck,
        CalendarDays,
        CircleCheck,
        ClipboardCheck,
        Hash,
        Info,
        PackageSearch,
        ScanLine,
        ShieldPlus,
    } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';

    export let auth = { user: null };

    let serial = '';
    let warrantyCode = '';
    let purchaseDate = '';

    $: canContinue = serial.trim().length >= 5 || warrantyCode.trim().length >= 5;

    const cardMotion = {
        hidden: { opacity: 0, y: 18, scale: 0.985 },
        show: i => ({
            opacity: 1,
            y: 0,
            scale: 1,
            transition: { delay: i * 0.06, duration: 0.34, ease: 'easeOut' },
        }),
    };
</script>

<DashboardShell title="فعال سازی گارانتی" {auth}>
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]">
        <motion.form
            class="rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)] sm:p-6"
            variants={cardMotion}
            initial="hidden"
            animate="show"
            custom={0}
            onsubmit={event => event.preventDefault()}
        >
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-sm font-black text-sky-700">
                        <ShieldPlus size={16} />
                        ثبت گارانتی محصول
                    </div>
                    <h2 class="mt-4 text-2xl font-black leading-9 text-slate-950">اطلاعات محصول</h2>
                </div>

                <div class="hidden size-14 items-center justify-center rounded-3xl bg-[#17275d] text-white sm:flex">
                    <ClipboardCheck size={26} />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="mb-2 block text-sm font-black text-slate-700">شماره سریال محصول</span>
                    <span class="relative block">
                        <Hash class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={19} />
                        <input
                            bind:value={serial}
                            dir="ltr"
                            autocomplete="off"
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-base font-black tracking-[0.05em] text-slate-950 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                            placeholder="Serial / SN"
                        />
                    </span>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-700">کد گارانتی</span>
                    <span class="relative block">
                        <BadgeCheck class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={19} />
                        <input
                            bind:value={warrantyCode}
                            dir="ltr"
                            autocomplete="off"
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-base font-black tracking-[0.05em] text-slate-950 outline-none transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                            placeholder="Warranty code"
                        />
                    </span>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-700">تاریخ خرید</span>
                    <span class="relative block">
                        <CalendarDays class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={19} />
                        <input
                            bind:value={purchaseDate}
                            inputmode="numeric"
                            dir="ltr"
                            autocomplete="off"
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-base font-black text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"
                            placeholder="1403/01/01"
                        />
                    </span>
                </label>
            </div>

            <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm font-bold leading-7 text-sky-800">
                <div class="flex items-start gap-2">
                    <Info class="mt-1 shrink-0" size={17} />
                    <span>فعلا فقط ظاهر صفحه آماده شده و فعال‌سازی نهایی بعد از قطعی شدن فیلدهای مهرسافت وصل می‌شود.</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button
                    type="button"
                    class="h-12 rounded-2xl px-4 text-sm font-black text-slate-500 transition hover:bg-slate-50 hover:text-slate-800"
                    on:click={() => {
                        serial = '';
                        warrantyCode = '';
                        purchaseDate = '';
                    }}
                >
                    پاک کردن فرم
                </button>

                <motion.button
                    type="button"
                    disabled={!canContinue}
                    class="inline-flex h-14 items-center justify-center gap-2 rounded-2xl bg-[#ec2228] px-6 text-base font-black text-white shadow-lg shadow-red-500/18 transition disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                    whileHover={canContinue ? { y: -2 } : {}}
                    whileTap={canContinue ? { scale: 0.97 } : {}}
                >
                    بررسی و ادامه
                    <ArrowLeft size={20} />
                </motion.button>
            </div>
        </motion.form>

        <div class="grid gap-5">
            <motion.div
                class="rounded-3xl bg-[#17275d] p-5 text-white shadow-[0_18px_60px_rgba(23,39,93,0.16)]"
                variants={cardMotion}
                initial="hidden"
                animate="show"
                custom={1}
            >
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-black text-white/64">وضعیت ثبت</div>
                        <div class="mt-2 text-2xl font-black">در انتظار اطلاعات</div>
                    </div>
                    <PackageSearch size={34} />
                </div>
            </motion.div>

            <motion.div
                class="rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)]"
                variants={cardMotion}
                initial="hidden"
                animate="show"
                custom={2}
            >
                <div class="mb-4 flex items-center gap-2 text-sm font-black text-slate-500">
                    <ScanLine size={17} />
                    مراحل فعال سازی
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CircleCheck class="text-sky-600" size={19} />
                        <span class="text-sm font-black text-slate-700">ورود اطلاعات محصول</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CircleCheck class="text-slate-300" size={19} />
                        <span class="text-sm font-black text-slate-500">بررسی با مهرسافت</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CircleCheck class="text-slate-300" size={19} />
                        <span class="text-sm font-black text-slate-500">ثبت نهایی گارانتی</span>
                    </div>
                </div>
            </motion.div>
        </div>
    </section>
</DashboardShell>
