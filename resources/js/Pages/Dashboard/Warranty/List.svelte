<script>
    import { router } from '@inertiajs/svelte';
    import { ChevronDown, ChevronLeft, ChevronRight, ChevronUp, Search, ShieldCheck } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Button } from '@/Components/ui/button';
    import * as Empty from '@/Components/ui/empty';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import * as Table from '@/Components/ui/table';

    export let auth = { user: null };
    export let warranties = {
        data: [],
        current_page: 1,
        from: null,
        last_page: 1,
        per_page: 25,
        to: null,
        total: 0,
    };
    export let filters = {
        search: '',
        sort: 'activated_at',
        direction: 'desc',
        per_page: 25,
    };

    let search = filters.search ?? '';
    let perPage = String(filters.per_page ?? 25);

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    function formatDate(value) {
        if (!value) {
            return 'ثبت نشده';
        }

        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));
    }

    function visit(overrides = {}) {
        router.get(
            routeUrl('admin.warranties.index', '/warranties'),
            {
                search,
                sort: filters.sort,
                direction: filters.direction,
                per_page: perPage,
                ...overrides,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function sortBy(column) {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';

        visit({
            sort: column,
            direction,
            page: 1,
        });
    }

    function changePage(page) {
        if (page < 1 || page > warranties.last_page || page === warranties.current_page) {
            return;
        }

        visit({ page });
    }

    function submitSearch() {
        visit({ page: 1 });
    }
</script>

<DashboardShell title="لیست گارانتی ها" {auth}>
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm lg:flex-row lg:items-end lg:justify-between lg:p-5">
            <div class="min-w-0">
                <h2 class="mt-1 text-lg font-black text-slate-950">جدول گارانتی‌های ثبت شده</h2>
            </div>

            <form class="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto" onsubmit={(event) => { event.preventDefault(); submitSearch(); }}>
                <Field.Group class="w-full sm:w-80">
                    <Field.Field>
                        <Field.Label for="warranty-search" class="sr-only">جست و جو</Field.Label>
                        <div class="relative">
                            <Input id="warranty-search" bind:value={search} placeholder="جست و جو سریال، مشتری یا شناسه پیگیری" class="h-10 pr-9" />
                            <Search class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        </div>
                    </Field.Field>
                </Field.Group>

                <select
                    class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700 sm:w-28"
                    bind:value={perPage}
                    onchange={() => visit({ per_page: perPage, page: 1 })}
                    aria-label="تعداد در صفحه"
                >
                    <option value="10">۱۰ ردیف</option>
                    <option value="25">۲۵ ردیف</option>
                    <option value="50">۵۰ ردیف</option>
                    <option value="100">۱۰۰ ردیف</option>
                </select>

                <Button type="submit" class="h-10 rounded-xl px-4 font-black">
                    <Search data-icon="inline-start" />
                    جست و جو
                </Button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
            {#if warranties.data.length}
                <Table.Root>
                    <Table.Header>
                        <Table.Row>
                            <Table.Head class="w-20 text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('id')}>
                                    شناسه
                                    {#if filters.sort === 'id'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('product_serial')}>
                                    محصول
                                    {#if filters.sort === 'product_serial'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('customer')}>
                                    مشتری
                                    {#if filters.sort === 'customer'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">نوع</Table.Head>
                            <Table.Head class="text-right">دوره</Table.Head>
                            <Table.Head class="text-right">شناسه پیگیری</Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('activated_at')}>
                                    فعال سازی
                                    {#if filters.sort === 'activated_at'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('expires_at')}>
                                    پایان
                                    {#if filters.sort === 'expires_at'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#each warranties.data as warranty}
                            <Table.Row>
                                <Table.Cell class="font-bold text-slate-500">#{warranty.id}</Table.Cell>
                                <Table.Cell>
                                    <div class="font-black text-slate-950">{warranty.product_name || 'عنوان محصول ثبت نشده'}</div>
                                    <div class="mt-1 text-xs font-bold text-slate-500">سریال محصول: <span dir="ltr">{warranty.product_serial}</span></div>
                                </Table.Cell>
                                <Table.Cell>
                                    <div class="font-black text-slate-950">{warranty.customer_name}</div>
                                    <div class="mt-1 text-xs font-bold text-slate-500" dir="ltr">{warranty.customer_mobile ?? ''}</div>
                                </Table.Cell>
                                <Table.Cell class="font-bold text-slate-600">{warranty.warranty_type ?? 'ثبت نشده'}</Table.Cell>
                                <Table.Cell class="font-bold text-slate-600">{warranty.warranty_period_months ?? 'نامشخص'} ماه</Table.Cell>
                                <Table.Cell>
                                    <div class="flex flex-wrap items-center gap-2">
                                        {warranty.mehrsoft_fix_no ?? 'ثبت نشده'}
                                    </div>
                                </Table.Cell>
                                <Table.Cell class="font-medium text-slate-500">{formatDate(warranty.activated_at)}</Table.Cell>
                                <Table.Cell class="font-medium text-slate-500">{formatDate(warranty.expires_at)}</Table.Cell>
                            </Table.Row>
                        {/each}
                    </Table.Body>
                </Table.Root>
            {:else}
                <Empty.Root class="py-14">
                    <Empty.Header>
                        <Empty.Media variant="icon">
                            <ShieldCheck />
                        </Empty.Media>
                        <Empty.Title>گارانتی پیدا نشد</Empty.Title>
                        <Empty.Description>با تغییر عبارت جست و جو یا حذف فیلترها دوباره بررسی کنید.</Empty.Description>
                    </Empty.Header>
                </Empty.Root>
            {/if}
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm font-bold text-slate-500">
                نمایش {warranties.from ?? 0} تا {warranties.to ?? 0} از {warranties.total} گارانتی
            </div>

            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    class="rounded-xl font-black"
                    disabled={warranties.current_page <= 1}
                    onclick={() => changePage(warranties.current_page - 1)}
                >
                    <ChevronRight data-icon="inline-start" />
                    قبلی
                </Button>
                <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm font-black text-slate-600">
                    صفحه {warranties.current_page} از {warranties.last_page}
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    class="rounded-xl font-black"
                    disabled={warranties.current_page >= warranties.last_page}
                    onclick={() => changePage(warranties.current_page + 1)}
                >
                    بعدی
                    <ChevronLeft data-icon="inline-end" />
                </Button>
            </div>
        </div>
    </div>
</DashboardShell>
