<script>
    import { router } from '@inertiajs/svelte';
    import { ChevronDown, ChevronLeft, ChevronRight, ChevronUp, Download, Search, Users } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import * as DropdownMenu from '@/Components/ui/dropdown-menu';
    import * as Empty from '@/Components/ui/empty';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import * as Table from '@/Components/ui/table';

    export let auth = { user: null };
    export let customers = {
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
        sort: 'registered_at',
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
            routeUrl('admin.customers.index', '/customers'),
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
        if (page < 1 || page > customers.last_page || page === customers.current_page) {
            return;
        }

        visit({ page });
    }

    function submitSearch() {
        visit({ page: 1 });
    }

    function exportUrl(scope) {
        const params = new URLSearchParams({
            scope,
            search: scope === 'current' ? search : '',
            sort: scope === 'current' ? filters.sort : 'registered_at',
            direction: scope === 'current' ? filters.direction : 'desc',
        });

        return `${routeUrl('admin.customers.export', '/customers/export')}?${params.toString()}`;
    }
</script>

<DashboardShell title="لیست مشتری ها" {auth}>
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm lg:flex-row lg:items-end lg:justify-between lg:p-5">
            <div class="min-w-0">
                <h2 class="mt-1 text-lg font-black text-slate-950">جدول مشتری‌های ثبت شده</h2>
            </div>

            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto">
                <form class="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto" onsubmit={(event) => { event.preventDefault(); submitSearch(); }}>
                    <Field.Group class="w-full sm:w-72">
                        <Field.Field>
                            <Field.Label for="customer-search" class="sr-only">جست و جو</Field.Label>
                            <div class="relative">
                                <Input id="customer-search" bind:value={search} placeholder="جست و جو نام یا موبایل" class="h-10 pr-9" />
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

                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        {#snippet child({ props })}
                            <Button variant="outline" class="h-10 rounded-xl px-4 font-black" {...props}>
                                <Download data-icon="inline-start" />
                                خروجی اکسل
                                <ChevronDown data-icon="inline-end" />
                            </Button>
                        {/snippet}
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="end" class="w-56" dir="rtl">
                        <DropdownMenu.Group>
                            <DropdownMenu.Item>
                                {#snippet child({ props })}
                                    <a href={exportUrl('all')} {...props}>خروجی همه کاربران</a>
                                {/snippet}
                            </DropdownMenu.Item>
                            <DropdownMenu.Item>
                                {#snippet child({ props })}
                                    <a href={exportUrl('current')} {...props}>خروجی لیست جاری</a>
                                {/snippet}
                            </DropdownMenu.Item>
                        </DropdownMenu.Group>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
            {#if customers.data.length}
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
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('name')}>
                                    نام
                                    {#if filters.sort === 'name'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('mobile')}>
                                    موبایل
                                    {#if filters.sort === 'mobile'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                            <Table.Head class="text-right">نقش</Table.Head>
                            <Table.Head class="text-right">
                                <Button variant="ghost" size="sm" class="font-black" onclick={() => sortBy('registered_at')}>
                                    تاریخ ثبت نام
                                    {#if filters.sort === 'registered_at'}
                                        {#if filters.direction === 'asc'}<ChevronUp data-icon="inline-end" />{:else}<ChevronDown data-icon="inline-end" />{/if}
                                    {/if}
                                </Button>
                            </Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#each customers.data as customer}
                            <Table.Row>
                                <Table.Cell class="font-bold text-slate-500">#{customer.id}</Table.Cell>
                                <Table.Cell class="font-black text-slate-950">{customer.name}</Table.Cell>
                                <Table.Cell class="font-bold text-slate-600" dir="ltr">{customer.mobile}</Table.Cell>
                                <Table.Cell>
                                    <Badge variant="secondary">مشتری</Badge>
                                </Table.Cell>
                                <Table.Cell class="font-medium text-slate-500">{formatDate(customer.registered_at)}</Table.Cell>
                            </Table.Row>
                        {/each}
                    </Table.Body>
                </Table.Root>
            {:else}
                <Empty.Root class="py-14">
                    <Empty.Header>
                        <Empty.Media variant="icon">
                            <Users />
                        </Empty.Media>
                        <Empty.Title>مشتری پیدا نشد</Empty.Title>
                        <Empty.Description>با تغییر عبارت جست و جو یا حذف فیلترها دوباره بررسی کنید.</Empty.Description>
                    </Empty.Header>
                </Empty.Root>
            {/if}
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm font-bold text-slate-500">
                نمایش {customers.from ?? 0} تا {customers.to ?? 0} از {customers.total} مشتری
            </div>

            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    class="rounded-xl font-black"
                    disabled={customers.current_page <= 1}
                    onclick={() => changePage(customers.current_page - 1)}
                >
                    <ChevronRight data-icon="inline-start" />
                    قبلی
                </Button>
                <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm font-black text-slate-600">
                    صفحه {customers.current_page} از {customers.last_page}
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    class="rounded-xl font-black"
                    disabled={customers.current_page >= customers.last_page}
                    onclick={() => changePage(customers.current_page + 1)}
                >
                    بعدی
                    <ChevronLeft data-icon="inline-end" />
                </Button>
            </div>
        </div>
    </div>
</DashboardShell>
