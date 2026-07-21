<script>
    import { Link } from '@inertiajs/svelte';
    import { ArrowLeft, BadgeCheck, CirclePlay, ExternalLink, ShieldPlus } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Button } from '@/Components/ui/button';
    import * as Card from '@/Components/ui/card';

    export let auth = { user: null };
    export let stats = {
        activeWarrantiesCount: 0,
    };

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    const installationVideos = [
        { title: 'نصب کولر ۲۴۰۰۰', href: 'https://www.aparat.com/v/gwpx2d3' },
        { title: 'نصب کولر ۲۸۰۰۰', href: 'https://www.aparat.com/v/mktff94' },
        { title: 'نصب کولر ۳۲۰۰۰', href: 'https://www.aparat.com/v/hgn87nx' },
    ];

    $: warrantiesHref = auth?.user?.is_manager
        ? routeUrl('admin.warranties.index', '/warranties')
        : routeUrl('warranties.mine', '/warranties/mine');
</script>

<DashboardShell title="داشبورد" {auth}>
    <section class="grid gap-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <Link
            href={warrantiesHref}
            class="group order-2 min-h-40 rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_22px_70px_rgba(23,39,93,0.12)] lg:order-1"
        >
            <div class="flex h-full flex-col justify-between gap-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-black text-slate-500">گارانتی های فعال</div>
                        <div class="mt-3 text-5xl font-black leading-none text-slate-950">
                            {stats.activeWarrantiesCount ?? 0}
                        </div>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                        <BadgeCheck size={24} />
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 text-sm font-black text-sky-700">
                    مشاهده لیست
                    <ArrowLeft class="transition group-hover:-translate-x-1" size={18} />
                </div>
            </div>
        </Link>

        <Link
            href={routeUrl('warranties.activate', '/warranties/activate')}
            class="group relative order-1 min-h-40 overflow-hidden rounded-3xl bg-[#17275d] p-5 text-white shadow-[0_18px_60px_rgba(23,39,93,0.18)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_80px_rgba(23,39,93,0.24)] lg:order-2"
        >
            <div class="absolute -left-10 -top-12 size-40 rounded-full bg-sky-400/20"></div>
            <div class="absolute -bottom-16 right-10 size-44 rounded-full bg-red-500/20"></div>

            <div class="relative flex h-full flex-col justify-between gap-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-black text-white/68">ثبت گارانتی</div>
                        <h2 class="mt-3 text-2xl font-black leading-9">فعال سازی گارانتی</h2>
                    </div>
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-white/12 text-white">
                        <ShieldPlus size={24} />
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 text-sm font-black text-white">
                    شروع فعال سازی
                    <ArrowLeft class="transition group-hover:-translate-x-1" size={18} />
                </div>
            </div>
        </Link>
    </section>

    <Card.Root class="mt-5 rounded-3xl shadow-sm">
        <Card.Header>
            <Card.Title>فیلم‌های آموزشی نصب</Card.Title>
            <Card.Description>برای مشاهده راهنمای نصب، مدل کولر را انتخاب کنید.</Card.Description>
        </Card.Header>

        <Card.Content>
            <div class="grid gap-3 md:grid-cols-3">
                {#each installationVideos as video (video.href)}
                    <Button
                        href={video.href}
                        target="_blank"
                        rel="noopener noreferrer"
                        variant="outline"
                        size="lg"
                        class="min-h-14 w-full justify-between rounded-2xl px-4"
                        aria-label={`${video.title} در پنجره جدید`}
                    >
                        <span class="inline-flex items-center gap-2">
                            <CirclePlay data-icon="inline-start" />
                            {video.title}
                        </span>
                        <ExternalLink data-icon="inline-end" />
                    </Button>
                {/each}
            </div>
        </Card.Content>
    </Card.Root>
</DashboardShell>
