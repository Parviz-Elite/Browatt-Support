<script>
    import { router } from '@inertiajs/svelte';
    import { ArrowDownUp, ChevronLeft, ChevronRight, Package, RefreshCw, Search, ShieldCheck } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import { Checkbox } from '@/Components/ui/checkbox';
    import * as Empty from '@/Components/ui/empty';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import { Spinner } from '@/Components/ui/spinner';
    import * as Table from '@/Components/ui/table';

    export let auth;
    export let products;
    export let filters;
    export let summary;
    export let defaultWarrantyMonths;

    const number = new Intl.NumberFormat('fa-IR');
    const date = new Intl.DateTimeFormat('fa-IR', { dateStyle: 'medium', timeStyle: 'short' });
    const routeUrl = (name, fallback, params) => typeof route === 'function' ? route(name, params) : fallback;

    let search = filters.search;
    let status = filters.status;
    let syncing = false;
    let saving = false;
    let revision = 0;
    let syncError = '';
    let progress = '';

    function visit(overrides = {}) {
        router.get(routeUrl('admin.products.index', '/products'), {
            search, status, sort: filters.sort, direction: filters.direction, ...overrides,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function sortBy(sort) {
        visit({ sort, direction: filters.sort === sort && filters.direction === 'asc' ? 'desc' : 'asc' });
    }

    async function sync() {
        syncing = true;
        syncError = '';
        progress = 'در حال دریافت فهرست کالاها از مهرپلاس…';

        try {
            const { data: catalog } = await window.axios.post(routeUrl('admin.products.sync', '/products/sync'));
            let after = 0;
            let checked = 0;
            do {
                progress = `بررسی مدت گارانتی ${number.format(checked)} از ${number.format(catalog.total)} کالا…`;
                const { data } = await window.axios.post(routeUrl('admin.products.sync-warranty', '/products/sync-warranty'), { after });
                checked += data.checked;
                after = data.next;
            } while (after !== null);

            progress = `همگام‌سازی کامل شد. مدت گارانتی ${number.format(checked)} کالا بررسی شد.`;
        } catch (error) {
            syncError = error.response?.data?.message ?? 'ارتباط قطع شد. همگام‌سازی را دوباره اجرا کنید.';
            progress = '';
        } finally {
            router.reload({ only: ['products', 'summary'], onFinish: () => { syncing = false; } });
        }
    }

    function updateActivation(product, enabled) {
        saving = true;
        router.patch(routeUrl('admin.products.update', `/products/${product.id}`, product.id), {
            online_activation_enabled: enabled,
        }, {
            preserveScroll: true,
            onFinish: () => {
                saving = false;
                revision += 1;
            },
        });
    }
</script>

<DashboardShell title="کالاها" {auth}>
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5" dir="rtl">
        <section class="rounded-2xl border border-border bg-card p-5 shadow-sm sm:p-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-muted text-primary">
                        <Package size={24} aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-foreground sm:text-2xl">مدیریت کالاها</h2>
                        <p class="mt-1 text-sm leading-7 text-muted-foreground">فهرست مهرپلاس، مدت گارانتی و امکان فعال‌سازی آنلاین هر کالا</p>
                    </div>
                </div>
                <Button class="min-h-11 shrink-0" disabled={syncing || saving} onclick={sync}>
                    {#if syncing}
                        <Spinner data-icon="inline-start" />
                    {:else}
                        <RefreshCw data-icon="inline-start" />
                    {/if}
                    {syncing ? 'در حال همگام‌سازی' : 'همگام‌سازی با مهرپلاس'}
                </Button>
            </div>
            <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-muted p-3 sm:p-4">
                    <dt class="text-xs text-muted-foreground">کالاهای پنل</dt>
                    <dd class="mt-1 text-xl font-black tabular-nums">{number.format(summary.total)}</dd>
                </div>
                <div class="rounded-xl bg-muted p-3 sm:p-4">
                    <dt class="text-xs text-muted-foreground">فعال‌سازی آنلاین غیرفعال</dt>
                    <dd class="mt-1 text-xl font-black tabular-nums">{number.format(summary.disabled)}</dd>
                </div>
                <div class="col-span-2 rounded-xl bg-muted p-3 sm:col-span-1 sm:p-4">
                    <dt class="text-xs text-muted-foreground">آخرین دریافت فهرست</dt>
                    <dd class="mt-1 font-bold">{summary.last_sync ? date.format(new Date(summary.last_sync)) : 'هنوز انجام نشده'}</dd>
                </div>
            </dl>
            <div aria-live="polite" class="text-sm text-muted-foreground">
                {#if progress}<p class="mt-4">{progress}</p>{/if}
            </div>
            {#if syncError}<p class="mt-4 text-sm text-destructive" role="alert">{syncError}</p>{/if}
        </section>

        <section class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm" aria-label="فهرست کالاها">
            <div class="flex flex-col gap-4 border-b border-border p-4 sm:p-5 2xl:flex-row 2xl:items-end 2xl:justify-between">
                <div>
                    <h3 class="font-black">فهرست کالاها</h3>
                    <p class="mt-1 text-xs leading-6 text-muted-foreground">تغییر تیک هر کالا بلافاصله ذخیره می‌شود.</p>
                </div>
                <form class="min-w-0" onsubmit={(event) => { event.preventDefault(); visit(); }}>
                    <Field.Group class="grid gap-2 sm:grid-cols-[minmax(0,16rem)_auto_auto] sm:items-center sm:justify-start">
                        <Field.Field>
                            <Field.Label for="product-search" class="sr-only">نام یا کد کالا</Field.Label>
                            <Input id="product-search" class="min-h-11" placeholder="جست‌وجوی نام یا کد کالا" bind:value={search} />
                        </Field.Field>
                        <Field.Field>
                            <Field.Label for="product-status" class="sr-only">وضعیت فعال‌سازی</Field.Label>
                            <select id="product-status" class="min-h-11 rounded-xl border border-border bg-background px-3 text-sm" bind:value={status} onchange={() => visit()}>
                                <option value="">همه وضعیت‌ها</option>
                                <option value="enabled">فعال‌سازی مجاز</option>
                                <option value="disabled">فعال‌سازی غیرفعال</option>
                            </select>
                        </Field.Field>
                        <Button type="submit" variant="outline" class="min-h-11">
                            <Search data-icon="inline-start" /> جست‌وجو
                        </Button>
                    </Field.Group>
                </form>
            </div>

            {#if products.data.length}
                <Table.Root>
                    <Table.Header class="bg-muted/60">
                        <Table.Row class="border-border">
                            <Table.Head class="px-3 text-right sm:px-5" aria-sort={filters.sort === 'name' ? (filters.direction === 'asc' ? 'ascending' : 'descending') : 'none'}>
                                <Button variant="ghost" size="sm" onclick={() => sortBy('name')}>کالا <ArrowDownUp data-icon="inline-end" /></Button>
                            </Table.Head>
                            <Table.Head class="hidden text-right md:table-cell" aria-sort={filters.sort === 'code' ? (filters.direction === 'asc' ? 'ascending' : 'descending') : 'none'}>
                                <Button variant="ghost" size="sm" onclick={() => sortBy('code')}>کد کالا <ArrowDownUp data-icon="inline-end" /></Button>
                            </Table.Head>
                            <Table.Head class="hidden text-right md:table-cell">مدت گارانتی</Table.Head>
                            <Table.Head class="px-3 text-center sm:px-5">فعال‌سازی آنلاین</Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#each products.data as product (product.id)}
                            <Table.Row class="border-border">
                                <Table.Cell class="w-full whitespace-normal px-3 py-4 sm:px-5 md:w-auto">
                                    <div class="font-bold">{product.name || 'نام در انتظار همگام‌سازی'}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground md:hidden">
                                        <bdi class="font-mono">{product.code}</bdi>
                                        {@render warranty(product)}
                                    </div>
                                </Table.Cell>
                                <Table.Cell class="hidden font-mono md:table-cell"><bdi>{product.code}</bdi></Table.Cell>
                                <Table.Cell class="hidden md:table-cell">{@render warranty(product)}</Table.Cell>
                                <Table.Cell class="px-3 py-3 sm:px-5">
                                    <div class="flex min-h-10 items-center justify-center gap-2">
                                        {#key revision}
                                            <Checkbox
                                                aria-label={`اجازه فعال‌سازی آنلاین برای ${product.name || product.code}`}
                                                checked={product.online_activation_enabled}
                                                disabled={saving || syncing}
                                                onCheckedChange={(checked) => updateActivation(product, checked === true)}
                                            />
                                        {/key}
                                        <Badge variant={product.online_activation_enabled ? 'secondary' : 'outline'}>
                                            {product.online_activation_enabled ? 'مجاز' : 'غیرفعال'}
                                        </Badge>
                                    </div>
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    </Table.Body>
                </Table.Root>
                <div class="flex items-center justify-between gap-3 border-t border-border px-3 py-3 sm:px-5">
                    <p class="text-xs text-muted-foreground">صفحهٔ {number.format(products.current_page)} از {number.format(products.last_page)}</p>
                    <div class="flex gap-2">
                        <Button variant="outline" size="icon" aria-label="صفحهٔ قبل" disabled={products.current_page === 1} onclick={() => visit({ page: products.current_page - 1 })}><ChevronRight /></Button>
                        <Button variant="outline" size="icon" aria-label="صفحهٔ بعد" disabled={products.current_page === products.last_page} onclick={() => visit({ page: products.current_page + 1 })}><ChevronLeft /></Button>
                    </div>
                </div>
            {:else}
                <Empty.Root class="p-8">
                    <Empty.Header>
                        <Empty.Title>کالایی یافت نشد</Empty.Title>
                        <Empty.Description>فیلترها را تغییر دهید یا فهرست مهرپلاس را همگام‌سازی کنید.</Empty.Description>
                    </Empty.Header>
                </Empty.Root>
            {/if}
        </section>
    </div>
</DashboardShell>

{#snippet warranty(product)}
    {#if product.warranty_months > 0}
        <Badge variant="secondary"><ShieldCheck data-icon="inline-start" /> {number.format(product.warranty_months)} ماه</Badge>
    {:else}
        <Badge variant="outline" title={product.warranty_checked_at ? 'مهرپلاس مدت معتبری اعلام نکرده است' : 'مدت گارانتی هنوز از مهرپلاس دریافت نشده است'}>
            {number.format(defaultWarrantyMonths)} ماه پیش‌فرض
        </Badge>
    {/if}
{/snippet}
