<script>
    import { motion } from '@humanspeak/svelte-motion';
    import { CalendarClock, PackageCheck, Plus, ShieldCheck } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import * as Empty from '@/Components/ui/empty';
    import * as Item from '@/Components/ui/item';

    export let auth = { user: null };
    export let warranties = [];

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    const syncLabels = {
        not_sent: 'ناموفق',
        pending: 'در انتظار همگام سازی',
        synced: 'همگام شده',
        failed: 'ناموفق',
    };

    function formatDate(value) {
        if (!value) {
            return 'ثبت نشده';
        }

        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: 'medium',
        }).format(new Date(value));
    }

    function syncLabel(status) {
        return syncLabels[status] ?? status ?? 'نامشخص';
    }

    function syncVariant(status) {
        return status === 'synced' ? 'secondary' : 'outline';
    }
</script>

<DashboardShell title="گارانتی های من" {auth}>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="min-w-0">
                <h2 class="text-lg font-black text-slate-950">گارانتی های فعال شده</h2>
            </div>

            <Button href={routeUrl('warranties.activate', '/warranties/activate')} class="h-10 rounded-xl px-4 font-black">
                <Plus data-icon="inline-start" />
                فعال سازی گارانتی
            </Button>
        </div>

        {#if warranties.length}
            <Item.Group class="bg-white shadow-sm">
                {#each warranties as warranty, index (warranty.id)}
                    <motion.div
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.035, duration: 0.2 }}
                    >
                        <Item.Root class="items-start gap-3 rounded-none px-4 py-4 sm:px-5">
                            <Item.Media variant="icon" class="mt-1 rounded-xl bg-red-50 p-2 text-[#ec2228]">
                                <ShieldCheck />
                            </Item.Media>

                            <Item.Content class="min-w-0 gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Item.Title class="text-base font-black text-slate-950">
                                        سریال <span dir="ltr">{warranty.product_serial}</span>
                                    </Item.Title>
                                    <Badge variant={syncVariant(warranty.mehrsoft_sync_status)}>{syncLabel(warranty.mehrsoft_sync_status)}</Badge>
                                </div>

                                <Item.Description class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                                    {#if warranty.product_code}
                                        <span>کد محصول: <span dir="ltr">{warranty.product_code}</span></span>
                                        <span>•</span>
                                    {/if}
                                    <span>نوع: {warranty.warranty_type ?? 'ثبت نشده'}</span>
                                    <span>•</span>
                                    <span>{warranty.warranty_period_months ?? 'نامشخص'} ماه</span>
                                </Item.Description>

                                <div class="mt-1 grid gap-2 text-xs font-bold text-slate-500 sm:grid-cols-3">
                                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                                        <CalendarClock />
                                        فعال سازی: {formatDate(warranty.activated_at)}
                                    </div>
                                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                                        <CalendarClock />
                                        شروع: {formatDate(warranty.starts_at)}
                                    </div>
                                    <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                                        <CalendarClock />
                                        پایان: {formatDate(warranty.expires_at)}
                                    </div>
                                </div>

                                {#if warranty.mehrsoft_document_no || warranty.mehrsoft_fix_no}
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        {#if warranty.mehrsoft_document_no}
                                            <Badge variant="outline">سند: {warranty.mehrsoft_document_no}</Badge>
                                        {/if}
                                        {#if warranty.mehrsoft_fix_no}
                                            <Badge variant="outline">شناسه پیگیری: {warranty.mehrsoft_fix_no}</Badge>
                                        {/if}
                                    </div>
                                {/if}
                            </Item.Content>
                        </Item.Root>
                    </motion.div>

                    {#if index !== warranties.length - 1}
                        <Item.Separator />
                    {/if}
                {/each}
            </Item.Group>
        {:else}
            <Empty.Root class="rounded-2xl bg-white py-14 shadow-sm">
                <Empty.Header>
                    <Empty.Media variant="icon">
                        <PackageCheck />
                    </Empty.Media>
                    <Empty.Title>هنوز گارانتی فعالی ندارید</Empty.Title>
                    <Empty.Description>برای ثبت اولین گارانتی، از دکمه فعال سازی استفاده کنید.</Empty.Description>
                </Empty.Header>
                <Empty.Content>
                    <Button href={routeUrl('warranties.activate', '/warranties/activate')}>
                        <Plus data-icon="inline-start" />
                        فعال سازی گارانتی
                    </Button>
                </Empty.Content>
            </Empty.Root>
        {/if}
    </div>
</DashboardShell>
