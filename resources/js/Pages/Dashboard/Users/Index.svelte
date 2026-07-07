<script>
    import { router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import { Edit3, KeyRound, UserCog } from '@lucide/svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import { Checkbox } from '@/Components/ui/checkbox';
    import * as Dialog from '@/Components/ui/dialog';
    import * as Empty from '@/Components/ui/empty';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import * as Item from '@/Components/ui/item';
    import { Separator } from '@/Components/ui/separator';
    import { Spinner } from '@/Components/ui/spinner';

    export let auth = { user: null };
    export let users = [];
    export let roles = [];

    let dialogOpen = false;
    let selectedUser = null;
    let form = {
        first_name: '',
        last_name: '',
        mobile: '',
        roles: [],
    };
    let errors = {};
    let processing = false;

    const roleLabels = {
        general_manager: 'مدیر کل',
        customer: 'مشتری',
    };
    const routeUrl = (name, fallback, params = undefined) => (typeof route === 'function' ? route(name, params) : fallback);

    function roleLabel(role) {
        const roleOption = roles.find((item) => item.name === role);

        return roleOption?.title || roleLabels[role] || role;
    }

    function formatDate(value) {
        if (!value) {
            return 'ثبت نشده';
        }

        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(new Date(value));
    }

    function splitName(user) {
        const [firstName = '', ...lastNameParts] = user.name === 'بدون نام' ? [] : user.name.split(' ');

        return {
            first_name: firstName,
            last_name: lastNameParts.join(' '),
        };
    }

    function openEditDialog(user) {
        const name = splitName(user);

        selectedUser = user;
        form = {
            first_name: user.first_name ?? name.first_name,
            last_name: user.last_name ?? name.last_name,
            mobile: user.mobile ?? '',
            roles: [...user.roles],
        };
        errors = {};
        processing = false;
        dialogOpen = true;
    }

    function closeDialog() {
        dialogOpen = false;
        selectedUser = null;
        form = {
            first_name: '',
            last_name: '',
            mobile: '',
            roles: [],
        };
        errors = {};
        processing = false;
    }

    function toggleRole(roleName) {
        if (form.roles.includes(roleName)) {
            form = {
                ...form,
                roles: form.roles.filter((item) => item !== roleName),
            };

            return;
        }

        form = {
            ...form,
            roles: [...form.roles, roleName],
        };
    }

    function submitUser() {
        if (!selectedUser) {
            return;
        }

        processing = true;
        errors = {};

        router.put(routeUrl('admin.users.update', `/users/${selectedUser.id}`, selectedUser.id), form, {
            preserveScroll: true,
            onSuccess: closeDialog,
            onError: (pageErrors) => {
                errors = pageErrors;
            },
            onFinish: () => {
                processing = false;
            },
        });
    }
</script>

<DashboardShell title="لیست کاربران" {auth}>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">
            <div class="text-sm font-black text-slate-400">کاربران داخلی</div>
            <h2 class="mt-1 text-lg font-black text-slate-950">کاربران مدیریتی و عملیاتی</h2>
            <p class="mt-2 text-sm font-medium leading-7 text-slate-500">
                این بخش برای کاربران کم تعداد پنل است. مشتری‌ها در صفحه جداگانه با جدول صفحه‌بندی‌شده نمایش داده می‌شوند.
            </p>
        </div>

        {#if users.length}
            <Item.Group class="bg-white shadow-sm">
                {#each users as user, index (user.id)}
                    <motion.div
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.035, duration: 0.2 }}
                    >
                        <Item.Root class="items-start gap-3 rounded-none px-4 py-4 sm:px-5">
                            <Item.Media variant="icon" class="mt-1 rounded-xl bg-red-50 p-2 text-[#ec2228]">
                                <UserCog />
                            </Item.Media>

                            <Item.Content class="min-w-0 gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Item.Title class="text-base font-black text-slate-950">{user.name}</Item.Title>
                                    {#each user.roles as role}
                                        <Badge variant="secondary">{roleLabel(role)}</Badge>
                                    {/each}
                                </div>

                                <Item.Description class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                                    <span dir="ltr">{user.mobile}</span>
                                    <span>•</span>
                                    <span>ثبت نام: {formatDate(user.registered_at)}</span>
                                </Item.Description>
                            </Item.Content>

                            <Item.Actions class="self-center">
                                <Button variant="outline" size="sm" class="rounded-xl font-black" onclick={() => openEditDialog(user)}>
                                    <Edit3 data-icon="inline-start" />
                                    ویرایش
                                </Button>
                            </Item.Actions>
                        </Item.Root>
                    </motion.div>

                    {#if index !== users.length - 1}
                        <Item.Separator />
                    {/if}
                {/each}
            </Item.Group>
        {:else}
            <Empty.Root class="rounded-2xl bg-white py-14 shadow-sm">
                <Empty.Header>
                    <Empty.Media variant="icon">
                        <KeyRound />
                    </Empty.Media>
                    <Empty.Title>کاربر داخلی ثبت نشده است</Empty.Title>
                    <Empty.Description>کاربران دارای نقش مشتری در صفحه لیست مشتری‌ها نمایش داده می‌شوند.</Empty.Description>
                </Empty.Header>
            </Empty.Root>
        {/if}
    </div>
</DashboardShell>

<Dialog.Root bind:open={dialogOpen}>
    <Dialog.Content class="max-h-[min(40rem,92vh)] overflow-y-auto sm:max-w-xl" dir="rtl">
        <Dialog.Header>
            <Dialog.Title>ویرایش کاربر</Dialog.Title>
            <Dialog.Description>اطلاعات پایه و نقش‌های کاربر را ویرایش کنید.</Dialog.Description>
        </Dialog.Header>

        <form class="flex flex-col gap-5" onsubmit={(event) => { event.preventDefault(); submitUser(); }}>
            <Field.Group>
                <div class="grid gap-3 sm:grid-cols-2">
                    <Field.Field data-invalid={Boolean(errors.first_name)}>
                        <Field.Label for="user-first-name">نام</Field.Label>
                        <Input id="user-first-name" bind:value={form.first_name} aria-invalid={Boolean(errors.first_name)} />
                        {#if errors.first_name}
                            <Field.Error>{errors.first_name}</Field.Error>
                        {/if}
                    </Field.Field>

                    <Field.Field data-invalid={Boolean(errors.last_name)}>
                        <Field.Label for="user-last-name">نام خانوادگی</Field.Label>
                        <Input id="user-last-name" bind:value={form.last_name} aria-invalid={Boolean(errors.last_name)} />
                        {#if errors.last_name}
                            <Field.Error>{errors.last_name}</Field.Error>
                        {/if}
                    </Field.Field>
                </div>

                <Field.Field data-invalid={Boolean(errors.mobile)}>
                    <Field.Label for="user-mobile">موبایل</Field.Label>
                    <Input id="user-mobile" dir="ltr" bind:value={form.mobile} aria-invalid={Boolean(errors.mobile)} />
                    {#if errors.mobile}
                        <Field.Error>{errors.mobile}</Field.Error>
                    {/if}
                </Field.Field>
            </Field.Group>

            <Separator />

            <Field.Set class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                <Field.Legend class="text-sm font-black text-slate-950">نقش‌ها</Field.Legend>
                <Field.Description>حداقل یک نقش باید برای کاربر انتخاب شود.</Field.Description>

                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    {#each roles as role (role.name)}
                        {@const checked = form.roles.includes(role.name)}
                        <button
                            type="button"
                            class={`flex min-h-16 items-start gap-3 rounded-xl border bg-white p-3 text-right transition ${
                                checked ? 'border-slate-950 shadow-sm' : 'border-slate-200 hover:border-slate-300'
                            }`}
                            onclick={() => toggleRole(role.name)}
                        >
                            <Checkbox checked={checked} tabindex="-1" aria-hidden="true" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-black text-slate-950">{role.title || roleLabel(role.name)}</span>
                                <span class="mt-1 block text-xs font-bold text-slate-400" dir="ltr">{role.name}</span>
                            </span>
                        </button>
                    {/each}
                </div>

                {#if errors.roles}
                    <Field.Error>{errors.roles}</Field.Error>
                {/if}
            </Field.Set>

            <Dialog.Footer class="gap-2 sm:justify-start">
                <Button type="submit" class="h-10 rounded-xl px-5 font-black" disabled={processing}>
                    {#if processing}
                        <Spinner data-icon="inline-start" />
                    {/if}
                    ذخیره تغییرات
                </Button>
                <Button type="button" variant="outline" class="h-10 rounded-xl px-5 font-black" onclick={closeDialog} disabled={processing}>
                    انصراف
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
