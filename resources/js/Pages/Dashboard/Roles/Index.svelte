<script>
    import { router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import { CheckCircle2, Edit3, KeyRound, Plus, ShieldCheck } from '@lucide/svelte';
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
    export let roles = [];
    export let permissionGroups = [];

    let dialogOpen = false;
    let selectedRole = null;
    let form = {
        title: '',
        name: '',
        permissions: [],
    };
    let errors = {};
    let processing = false;

    const routeUrl = (name, fallback, params = undefined) => (typeof route === 'function' ? route(name, params) : fallback);
    const systemRoleLabels = {
        general_manager: 'مدیر کل',
        customer: 'مشتری',
    };

    $: allPermissionCount = permissionGroups.reduce((total, group) => total + group.permissions.length, 0);
    $: selectedPermissionCount = form.permissions.length;
    $: isEditing = Boolean(selectedRole);

    function roleLabel(role) {
        return role.title || systemRoleLabels[role.name] || role.name;
    }

    function permissionLabel(permissionName) {
        for (const group of permissionGroups) {
            const permission = group.permissions.find((item) => item.name === permissionName);

            if (permission) {
                return permission.label;
            }
        }

        return permissionName;
    }

    function resetForm() {
        selectedRole = null;
        form = {
            title: '',
            name: '',
            permissions: [],
        };
        errors = {};
        processing = false;
    }

    function openCreateDialog() {
        resetForm();
        dialogOpen = true;
    }

    function openEditDialog(role) {
        selectedRole = role;
        form = {
            title: role.title || roleLabel(role),
            name: role.name,
            permissions: [...role.permissions],
        };
        errors = {};
        processing = false;
        dialogOpen = true;
    }

    function closeDialog() {
        dialogOpen = false;
        resetForm();
    }

    function togglePermission(permissionName) {
        if (form.permissions.includes(permissionName)) {
            form = {
                ...form,
                permissions: form.permissions.filter((item) => item !== permissionName),
            };

            return;
        }

        form = {
            ...form,
            permissions: [...form.permissions, permissionName],
        };
    }

    function setGroupPermissions(group, enabled) {
        const names = group.permissions.map((permission) => permission.name);
        const nextPermissions = enabled
            ? Array.from(new Set([...form.permissions, ...names]))
            : form.permissions.filter((permissionName) => !names.includes(permissionName));

        form = {
            ...form,
            permissions: nextPermissions,
        };
    }

    function submitRole() {
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

        if (isEditing) {
            router.put(routeUrl('admin.roles.update', `/roles/${selectedRole.id}`, selectedRole.id), form, options);
            return;
        }

        router.post(routeUrl('admin.roles.store', '/roles'), form, options);
    }
</script>

<DashboardShell title="لیست نقش ها" {auth}>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="min-w-0">
                <div class="text-sm font-black text-slate-400">مدیریت دسترسی ها</div>
                <h2 class="mt-1 text-lg font-black text-slate-950">نقش های سیستم</h2>
                <p class="mt-2 text-sm font-medium leading-7 text-slate-500">
                    برای هر نقش می توانید مجموعه دسترسی های متفاوت تعریف کنید. نقش های سیستمی قابل تغییر نام نیستند.
                </p>
            </div>

            <Button class="h-10 rounded-xl px-4 font-black" onclick={openCreateDialog}>
                <Plus data-icon="inline-start" />
                نقش جدید
            </Button>
        </div>

        {#if roles.length}
            <Item.Group class="bg-white shadow-sm">
                {#each roles as role, index (role.id)}
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
                                    <Item.Title class="text-base font-black text-slate-950">{roleLabel(role)}</Item.Title>
                                    <Badge variant={role.is_system ? 'secondary' : 'outline'}>
                                        {role.is_system ? 'سیستمی' : 'سفارشی'}
                                    </Badge>
                                </div>

                                <Item.Description class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                                    <span dir="ltr">{role.name}</span>
                                    <span>•</span>
                                    <span>{role.users_count} کاربر</span>
                                    <span>•</span>
                                    <span>{role.permissions.length} دسترسی</span>
                                </Item.Description>

                                {#if role.permissions.length}
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        {#each role.permissions.slice(0, 5) as permissionName}
                                            <Badge variant="outline">{permissionLabel(permissionName)}</Badge>
                                        {/each}
                                        {#if role.permissions.length > 5}
                                            <Badge variant="secondary">+{role.permissions.length - 5}</Badge>
                                        {/if}
                                    </div>
                                {/if}
                            </Item.Content>

                            <Item.Actions class="self-center">
                                <Button variant="outline" size="sm" class="rounded-xl font-black" onclick={() => openEditDialog(role)}>
                                    <Edit3 data-icon="inline-start" />
                                    ویرایش
                                </Button>
                            </Item.Actions>
                        </Item.Root>
                    </motion.div>

                    {#if index !== roles.length - 1}
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
                    <Empty.Title>نقشی ثبت نشده است</Empty.Title>
                    <Empty.Description>اولین نقش را همراه دسترسی های مورد نیاز ایجاد کنید.</Empty.Description>
                </Empty.Header>
                <Empty.Content>
                    <Button onclick={openCreateDialog}>
                        <Plus data-icon="inline-start" />
                        نقش جدید
                    </Button>
                </Empty.Content>
            </Empty.Root>
        {/if}
    </div>
</DashboardShell>

<Dialog.Root bind:open={dialogOpen}>
    <Dialog.Content class="max-h-[min(42rem,92vh)] overflow-y-auto sm:max-w-2xl" dir="rtl">
        <Dialog.Header>
            <Dialog.Title>{isEditing ? 'ویرایش نقش' : 'ایجاد نقش جدید'}</Dialog.Title>
            <Dialog.Description>
                عنوان نمایشی، شناسه انگلیسی نقش و دسترسی های مجاز برای این نقش را مشخص کنید.
            </Dialog.Description>
        </Dialog.Header>

        <form class="flex flex-col gap-5" onsubmit={(event) => { event.preventDefault(); submitRole(); }}>
            <Field.Group>
                <Field.Field data-invalid={Boolean(errors.title)}>
                    <Field.Label for="role-title">عنوان نقش</Field.Label>
                    <Input
                        id="role-title"
                        placeholder="مثلا مدیر پشتیبانی"
                        bind:value={form.title}
                        aria-invalid={Boolean(errors.title)}
                    />
                    <Field.Description>این عنوان در پنل برای مدیران نمایش داده می‌شود.</Field.Description>
                    {#if errors.title}
                        <Field.Error>{errors.title}</Field.Error>
                    {/if}
                </Field.Field>

                <Field.Field data-invalid={Boolean(errors.name)}>
                    <Field.Label for="role-name">شناسه انگلیسی نقش</Field.Label>
                    <Input
                        id="role-name"
                        dir="ltr"
                        placeholder="support_manager"
                        bind:value={form.name}
                        disabled={selectedRole?.is_system}
                        aria-invalid={Boolean(errors.name)}
                    />
                    <Field.Description>
                        این مقدار همان slug سیستمی نقش است؛ از حروف انگلیسی، عدد، خط تیره یا زیرخط استفاده کنید.
                    </Field.Description>
                    {#if errors.name}
                        <Field.Error>{errors.name}</Field.Error>
                    {/if}
                </Field.Field>
            </Field.Group>

            <Separator />

            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-black text-slate-950">دسترسی ها</div>
                    <div class="mt-1 text-xs font-bold text-slate-500">{selectedPermissionCount} از {allPermissionCount} دسترسی انتخاب شده</div>
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="rounded-xl font-black"
                    onclick={() => (form = { ...form, permissions: permissionGroups.flatMap((group) => group.permissions.map((permission) => permission.name)) })}
                >
                    <CheckCircle2 data-icon="inline-start" />
                    انتخاب همه
                </Button>
            </div>

            <div class="flex flex-col gap-4">
                {#each permissionGroups as group (group.key)}
                    {@const selectedInGroup = group.permissions.filter((permission) => form.permissions.includes(permission.name)).length}
                    {@const groupChecked = selectedInGroup === group.permissions.length && group.permissions.length > 0}

                    <Field.Set class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <Field.Legend class="text-sm font-black text-slate-950">{group.label}</Field.Legend>
                                <Field.Description>{selectedInGroup} دسترسی از {group.permissions.length}</Field.Description>
                            </div>

                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                class="rounded-xl font-black"
                                onclick={() => setGroupPermissions(group, !groupChecked)}
                            >
                                {groupChecked ? 'برداشتن گروه' : 'انتخاب گروه'}
                            </Button>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            {#each group.permissions as permission (permission.name)}
                                {@const checked = form.permissions.includes(permission.name)}
                                <button
                                    type="button"
                                    class={`flex min-h-20 items-start gap-3 rounded-xl border bg-white p-3 text-right transition ${
                                        checked ? 'border-slate-950 shadow-sm' : 'border-slate-200 hover:border-slate-300'
                                    }`}
                                    onclick={() => togglePermission(permission.name)}
                                >
                                    <Checkbox checked={checked} tabindex="-1" aria-hidden="true" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-black text-slate-950">{permission.label}</span>
                                        {#if permission.description}
                                            <span class="mt-1 block text-xs font-medium leading-6 text-slate-500">{permission.description}</span>
                                        {/if}
                                        <span class="mt-1 block text-xs font-bold text-slate-400" dir="ltr">{permission.name}</span>
                                    </span>
                                </button>
                            {/each}
                        </div>
                    </Field.Set>
                {/each}
            </div>

            {#if errors.permissions}
                <div class="text-sm font-bold text-red-600">{errors.permissions}</div>
            {/if}

            <Dialog.Footer class="gap-2 sm:justify-start">
                <Button type="submit" class="h-10 rounded-xl px-5 font-black" disabled={processing}>
                    {#if processing}
                        <Spinner data-icon="inline-start" />
                    {/if}
                    {isEditing ? 'ذخیره تغییرات' : 'ثبت نقش'}
                </Button>
                <Button type="button" variant="outline" class="h-10 rounded-xl px-5 font-black" onclick={closeDialog} disabled={processing}>
                    انصراف
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
