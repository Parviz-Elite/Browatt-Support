<script>
    import { router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import { Edit3, KeyRound, UserCog, UserPlus } from '@lucide/svelte';
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

    $: isGeneralManager = auth?.user?.roles?.includes('general_manager') ?? false;
    $: canCreateUser = isGeneralManager || (auth?.user?.permissions?.includes('roles.manage') ?? false);
    $: availableRoles = selectedUser
        ? roles
        : roles.filter((role) => role.name !== 'customer' && (isGeneralManager || role.name !== 'general_manager'));
    $: roleError = errors.roles || Object.entries(errors).find(([key]) => key.startsWith('roles.'))?.[1];

    function openCreateDialog() {
        closeDialog();
        dialogOpen = true;
    }

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
        if (processing || (!selectedUser && !canCreateUser)) {
            return;
        }

        processing = true;
        errors = {};

        const options = {
            preserveScroll: true,
            onSuccess: closeDialog,
            onError: (pageErrors) => {
                errors = pageErrors;
            },
            onFinish: () => {
                processing = false;
            },
        };

        if (selectedUser) {
            router.put(routeUrl('admin.users.update', `/users/${selectedUser.id}`, selectedUser.id), form, options);
        } else {
            router.post(routeUrl('admin.users.store', '/users'), form, options);
        }
    }
</script>

<DashboardShell title="لیست کاربران" {auth}>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <div class="text-sm font-black text-slate-400">کاربران داخلی</div>
                <h2 class="mt-1 text-lg font-black text-slate-950">کاربران مدیریتی و عملیاتی</h2>
                <p class="mt-2 text-sm font-medium leading-7 text-slate-500">
                    این بخش برای کاربران کم تعداد پنل است. مشتری‌ها در صفحه جداگانه با جدول صفحه‌بندی‌شده نمایش داده می‌شوند.
                </p>
            </div>
            {#if canCreateUser}
                <Button size="lg" class="w-full shrink-0 sm:w-auto" onclick={openCreateDialog}>
                    <UserPlus data-icon="inline-start" />
                    افزودن کاربر جدید
                </Button>
            {/if}
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
    <Dialog.Content class="max-h-[min(40rem,92dvh)] overflow-y-auto sm:max-w-xl" dir="rtl" showCloseButton={!processing} onEscapeKeydown={(event) => { if (processing) event.preventDefault(); }} onInteractOutside={(event) => { if (processing) event.preventDefault(); }}>
        <Dialog.Header>
            <Dialog.Title>{selectedUser ? 'ویرایش کاربر' : 'افزودن کاربر جدید'}</Dialog.Title>
            <Dialog.Description>
                {selectedUser ? 'اطلاعات پایه و نقش‌های کاربر را ویرایش کنید.' : 'کاربر داخلی را با نام، موبایل و نقش ثبت کنید. ورود او با کد پیامکی همین شماره خواهد بود.'}
            </Dialog.Description>
        </Dialog.Header>

        <form class="flex flex-col gap-5" onsubmit={(event) => { event.preventDefault(); submitUser(); }}>
            <Field.FieldGroup>
                <Field.FieldGroup class="gap-3 sm:grid sm:grid-cols-2">
                    <Field.Field data-invalid={Boolean(errors.first_name)}>
                        <Field.Label for="user-first-name">نام</Field.Label>
                        <Input id="user-first-name" autocomplete="given-name" maxlength={80} required={!selectedUser} disabled={processing} bind:value={form.first_name} aria-invalid={Boolean(errors.first_name)} />
                        {#if errors.first_name}
                            <Field.Error>{errors.first_name}</Field.Error>
                        {/if}
                    </Field.Field>

                    <Field.Field data-invalid={Boolean(errors.last_name)}>
                        <Field.Label for="user-last-name">نام خانوادگی</Field.Label>
                        <Input id="user-last-name" autocomplete="family-name" maxlength={80} required={!selectedUser} disabled={processing} bind:value={form.last_name} aria-invalid={Boolean(errors.last_name)} />
                        {#if errors.last_name}
                            <Field.Error>{errors.last_name}</Field.Error>
                        {/if}
                    </Field.Field>
                </Field.FieldGroup>

                <Field.Field data-invalid={Boolean(errors.mobile)}>
                    <Field.Label for="user-mobile">موبایل</Field.Label>
                    <Input id="user-mobile" type="tel" inputmode="tel" autocomplete="tel" maxlength={32} placeholder="09123456789" required disabled={processing} dir="ltr" bind:value={form.mobile} aria-invalid={Boolean(errors.mobile)} />
                    {#if errors.mobile}
                        <Field.Error>{errors.mobile}</Field.Error>
                    {/if}
                </Field.Field>
            </Field.FieldGroup>

            <Separator />

            <Field.FieldSet disabled={processing} data-invalid={Boolean(roleError)}>
                <Field.FieldLegend>نقش‌ها</Field.FieldLegend>
                <Field.Description id="user-roles-description">حداقل یک نقش باید برای کاربر انتخاب شود.</Field.Description>

                <Field.FieldGroup class="gap-3 sm:grid sm:grid-cols-2">
                    {#each availableRoles as role (role.name)}
                        {@const checked = form.roles.includes(role.name)}
                        <Field.Field orientation="horizontal" data-invalid={Boolean(roleError)}>
                            <Checkbox id={`user-role-${role.name}`} checked={checked} onCheckedChange={() => toggleRole(role.name)} disabled={processing} aria-invalid={Boolean(roleError)} aria-describedby="user-roles-description" />
                            <Field.FieldLabel for={`user-role-${role.name}`} class="min-h-11 flex-1 items-center">{role.title || roleLabel(role.name)}</Field.FieldLabel>
                        </Field.Field>
                    {/each}
                </Field.FieldGroup>

                {#if roleError}
                    <Field.Error>{roleError}</Field.Error>
                {/if}
            </Field.FieldSet>

            <Dialog.Footer class="gap-2 sm:justify-start">
                <Button type="submit" class="h-10 rounded-xl px-5 font-black" disabled={processing}>
                    {#if processing}
                        <Spinner data-icon="inline-start" />
                    {/if}
                    {selectedUser ? 'ذخیره تغییرات' : 'افزودن کاربر'}
                </Button>
                <Button type="button" variant="outline" class="h-10 rounded-xl px-5 font-black" onclick={closeDialog} disabled={processing}>
                    انصراف
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
