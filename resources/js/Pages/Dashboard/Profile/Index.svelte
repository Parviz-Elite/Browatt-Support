<script>
    import { router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import { Edit3, Home, MapPin, Plus, Trash2 } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import * as Dialog from '@/Components/ui/dialog';
    import * as Empty from '@/Components/ui/empty';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import * as Item from '@/Components/ui/item';
    import { Spinner } from '@/Components/ui/spinner';
    import { Textarea } from '@/Components/ui/textarea';

    export let auth = { user: null };
    export let profile = {
        first_name: '',
        last_name: '',
        mobile: '',
        national_code: '',
        cust_type: 0,
        cust_sex: 1,
        cust_name: '',
        registered_at: null,
    };
    export let addresses = [];

    let profileForm = {
        first_name: profile.first_name ?? '',
        last_name: profile.last_name ?? '',
        national_code: profile.national_code ?? '',
        cust_type: String(profile.cust_type ?? '0'),
        cust_sex: String(profile.cust_sex ?? '1'),
        cust_name: profile.cust_name ?? '',
    };
    let profileErrors = {};
    let profileProcessing = false;

    let addressDialogOpen = false;
    let selectedAddress = null;
    let addressForm = emptyAddress();
    let addressErrors = {};
    let addressProcessing = false;

    const routeUrl = (name, fallback, params = undefined) => (typeof route === 'function' ? route(name, params) : fallback);
    $: nationalCodeValid = !profileForm.national_code || isValidNationalCode(profileForm.national_code);

    function isValidNationalCode(value) {
        const code = String(value ?? '').replace(/\D/g, '');

        if (!/^\d{10}$/.test(code) || /^(\d)\1{9}$/.test(code)) {
            return false;
        }

        const sum = code
            .slice(0, 9)
            .split('')
            .reduce((total, digit, index) => total + Number(digit) * (10 - index), 0);
        const remainder = sum % 11;
        const checkDigit = Number(code[9]);

        return remainder < 2 ? checkDigit === remainder : checkDigit === 11 - remainder;
    }

    function validationFeedback() {
        toast.error('ذخیره انجام نشد. لطفا خطاهای فرم را بررسی کنید.');
    }

    function emptyAddress() {
        return {
            title: '',
            province: '',
            city: '',
            address: '',
            is_default: false,
        };
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

    function submitProfile() {
        if (!nationalCodeValid) {
            profileErrors = {
                ...profileErrors,
                national_code: 'کد ملی وارد شده معتبر نیست.',
            };
            validationFeedback();
            return;
        }

        profileProcessing = true;
        profileErrors = {};

        router.put(routeUrl('profile.update', '/profile'), profileForm, {
            preserveScroll: true,
            onError: (errors) => {
                profileErrors = errors;
                validationFeedback();
            },
            onFinish: () => {
                profileProcessing = false;
            },
        });
    }

    function openCreateAddress() {
        selectedAddress = null;
        addressForm = emptyAddress();
        addressErrors = {};
        addressProcessing = false;
        addressDialogOpen = true;
    }

    function openEditAddress(address) {
        selectedAddress = address;
        addressForm = {
            title: address.title ?? '',
            province: address.province ?? '',
            city: address.city ?? '',
            address: address.address ?? '',
            is_default: Boolean(address.is_default),
        };
        addressErrors = {};
        addressProcessing = false;
        addressDialogOpen = true;
    }

    function closeAddressDialog() {
        addressDialogOpen = false;
        selectedAddress = null;
        addressForm = emptyAddress();
        addressErrors = {};
        addressProcessing = false;
    }

    function submitAddress() {
        addressProcessing = true;
        addressErrors = {};

        const options = {
            preserveScroll: true,
            onSuccess: closeAddressDialog,
            onError: (errors) => {
                addressErrors = errors;
            },
            onFinish: () => {
                addressProcessing = false;
            },
        };

        if (selectedAddress) {
            router.put(routeUrl('profile.addresses.update', `/profile/addresses/${selectedAddress.id}`, selectedAddress.id), addressForm, options);
            return;
        }

        router.post(routeUrl('profile.addresses.store', '/profile/addresses'), addressForm, options);
    }

    function deleteAddress(address) {
        if (!confirm('این آدرس حذف شود؟')) {
            return;
        }

        router.delete(routeUrl('profile.addresses.destroy', `/profile/addresses/${address.id}`, address.id), {
            preserveScroll: true,
        });
    }
</script>

<DashboardShell title="پروفایل" {auth}>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-5">
        <div class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">
            <h2 class="text-lg font-black text-slate-950">اطلاعات کاربری</h2>

            <form class="mt-5 flex flex-col gap-5" onsubmit={(event) => { event.preventDefault(); submitProfile(); }}>
                <Field.Group>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <Field.Field data-invalid={Boolean(profileErrors.first_name)}>
                            <Field.Label for="profile-first-name">نام</Field.Label>
                            <Input id="profile-first-name" bind:value={profileForm.first_name} aria-invalid={Boolean(profileErrors.first_name)} />
                            {#if profileErrors.first_name}
                                <Field.Error>{profileErrors.first_name}</Field.Error>
                            {/if}
                        </Field.Field>

                        <Field.Field data-invalid={Boolean(profileErrors.last_name)}>
                            <Field.Label for="profile-last-name">نام خانوادگی</Field.Label>
                            <Input id="profile-last-name" bind:value={profileForm.last_name} aria-invalid={Boolean(profileErrors.last_name)} />
                            {#if profileErrors.last_name}
                                <Field.Error>{profileErrors.last_name}</Field.Error>
                            {/if}
                        </Field.Field>
                    </div>

                    <Field.Field data-invalid={Boolean(profileErrors.national_code) || !nationalCodeValid}>
                        <Field.Label for="profile-national-code">کد ملی</Field.Label>
                        <Input
                            id="profile-national-code"
                            dir="ltr"
                            bind:value={profileForm.national_code}
                            aria-invalid={Boolean(profileErrors.national_code) || !nationalCodeValid}
                            maxlength="10"
                        />
                        {#if profileErrors.national_code}
                            <Field.Error>{profileErrors.national_code}</Field.Error>
                        {:else if !nationalCodeValid}
                            <Field.Error>کد ملی وارد شده معتبر نیست.</Field.Error>
                        {/if}
                    </Field.Field>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <Field.Field data-invalid={Boolean(profileErrors.cust_type)}>
                            <Field.Label for="profile-cust-type">نوع مشتری</Field.Label>
                            <select id="profile-cust-type" bind:value={profileForm.cust_type} class="h-10 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none">
                                <option value="0">حقیقی</option>
                                <option value="1">حقوقی</option>
                            </select>
                            {#if profileErrors.cust_type}
                                <Field.Error>{profileErrors.cust_type}</Field.Error>
                            {/if}
                        </Field.Field>

                        <Field.Field data-invalid={Boolean(profileErrors.cust_sex)}>
                            <Field.Label for="profile-cust-sex">جنسیت</Field.Label>
                            <select id="profile-cust-sex" bind:value={profileForm.cust_sex} class="h-10 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none">
                                <option value="0">خانم</option>
                                <option value="1">آقا</option>
                            </select>
                            {#if profileErrors.cust_sex}
                                <Field.Error>{profileErrors.cust_sex}</Field.Error>
                            {/if}
                        </Field.Field>
                    </div>

                    {#if profileForm.cust_type === '1'}
                        <Field.Field data-invalid={Boolean(profileErrors.cust_name)}>
                            <Field.Label for="profile-cust-name">نام شخص حقوقی</Field.Label>
                            <Input id="profile-cust-name" bind:value={profileForm.cust_name} aria-invalid={Boolean(profileErrors.cust_name)} />
                            {#if profileErrors.cust_name}
                                <Field.Error>{profileErrors.cust_name}</Field.Error>
                            {/if}
                        </Field.Field>
                    {/if}

                    <div class="grid gap-3 sm:grid-cols-2">
                        <Field.Field data-disabled>
                            <Field.Label for="profile-mobile">موبایل</Field.Label>
                            <Input id="profile-mobile" dir="ltr" value={profile.mobile ?? ''} disabled />
                            <Field.Description>شماره موبایل شناسه ورود شماست و قابل تغییر نیست.</Field.Description>
                        </Field.Field>

                        <Field.Field data-disabled>
                            <Field.Label for="profile-registered-at">تاریخ ثبت نام</Field.Label>
                            <Input id="profile-registered-at" value={formatDate(profile.registered_at)} disabled />
                        </Field.Field>
                    </div>
                </Field.Group>

                <div>
                    <Button type="submit" class="h-10 rounded-xl px-5 font-black" disabled={profileProcessing}>
                        {#if profileProcessing}
                            <Spinner data-icon="inline-start" />
                        {/if}
                        ذخیره اطلاعات
                    </Button>
                </div>
            </form>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h2 class="text-lg font-black text-slate-950">آدرس‌ها</h2>
            </div>

            <Button class="h-10 rounded-xl px-4 font-black" onclick={openCreateAddress}>
                <Plus data-icon="inline-start" />
                آدرس جدید
            </Button>
        </div>

        {#if addresses.length}
            <Item.Group class="bg-white shadow-sm">
                {#each addresses as address, index (address.id)}
                    <motion.div
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.035, duration: 0.2 }}
                    >
                        <Item.Root class="items-start gap-3 rounded-none px-4 py-4 sm:px-5">
                            <Item.Media variant="icon" class="mt-1 rounded-xl bg-red-50 p-2 text-[#ec2228]">
                                <MapPin />
                            </Item.Media>

                            <Item.Content class="min-w-0 gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Item.Title class="text-base font-black text-slate-950">{address.title || (address.is_default ? 'آدرس پیش فرض' : 'آدرس')}</Item.Title>
                                    {#if address.is_default}
                                        <Badge variant="secondary">پیش فرض</Badge>
                                    {/if}
                                </div>

                                <Item.Description class="flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                                    <span>{[address.province, address.city].filter(Boolean).join('، ') || 'موقعیت ثبت نشده'}</span>
                                </Item.Description>

                                <p class="text-sm font-medium leading-7 text-slate-600">{address.address}</p>
                            </Item.Content>

                            <Item.Actions class="self-center">
                                <div class="flex items-center gap-2">
                                    <Button variant="outline" size="sm" class="rounded-xl font-black" onclick={() => openEditAddress(address)}>
                                        <Edit3 data-icon="inline-start" />
                                        ویرایش
                                    </Button>
                                    <Button variant="destructive" size="icon-sm" aria-label="حذف آدرس" onclick={() => deleteAddress(address)}>
                                        <Trash2 />
                                    </Button>
                                </div>
                            </Item.Actions>
                        </Item.Root>
                    </motion.div>

                    {#if index !== addresses.length - 1}
                        <Item.Separator />
                    {/if}
                {/each}
            </Item.Group>
        {:else}
            <Empty.Root class="rounded-2xl bg-white py-14 shadow-sm">
                <Empty.Header>
                    <Empty.Media variant="icon">
                        <Home />
                    </Empty.Media>
                    <Empty.Title>هنوز آدرسی ثبت نشده است</Empty.Title>
                    <Empty.Description>برای استفاده در درخواست‌های خدمات، آدرس‌های خود را ثبت کنید.</Empty.Description>
                </Empty.Header>
                <Empty.Content>
                    <Button onclick={openCreateAddress}>
                        <Plus data-icon="inline-start" />
                        آدرس جدید
                    </Button>
                </Empty.Content>
            </Empty.Root>
        {/if}
    </div>
</DashboardShell>

<Dialog.Root bind:open={addressDialogOpen}>
    <Dialog.Content class="max-h-[min(42rem,92vh)] overflow-y-auto sm:max-w-2xl" dir="rtl">
        <Dialog.Header>
            <Dialog.Title>{selectedAddress ? 'ویرایش آدرس' : 'آدرس جدید'}</Dialog.Title>
            <Dialog.Description>اطلاعات آدرس را وارد کنید.</Dialog.Description>
        </Dialog.Header>

        <form class="flex flex-col gap-5" onsubmit={(event) => { event.preventDefault(); submitAddress(); }}>
            <Field.Group>
                <Field.Field data-invalid={Boolean(addressErrors.title)}>
                    <Field.Label for="address-title">عنوان آدرس</Field.Label>
                    <Input id="address-title" bind:value={addressForm.title} placeholder="مثال: خانه، دفتر و..." aria-invalid={Boolean(addressErrors.title)} />
                    {#if addressErrors.title}
                        <Field.Error>{addressErrors.title}</Field.Error>
                    {/if}
                </Field.Field>

                <div class="grid gap-3 sm:grid-cols-2">
                    <Field.Field data-invalid={Boolean(addressErrors.province)}>
                        <Field.Label for="province">استان</Field.Label>
                        <Input id="province" bind:value={addressForm.province} aria-invalid={Boolean(addressErrors.province)} />
                    </Field.Field>

                    <Field.Field data-invalid={Boolean(addressErrors.city)}>
                        <Field.Label for="city">شهر</Field.Label>
                        <Input id="city" bind:value={addressForm.city} aria-invalid={Boolean(addressErrors.city)} />
                    </Field.Field>
                </div>

                <Field.Field data-invalid={Boolean(addressErrors.address)}>
                    <Field.Label for="address">آدرس کامل</Field.Label>
                    <Textarea id="address" rows="4" bind:value={addressForm.address} aria-invalid={Boolean(addressErrors.address)} />
                    {#if addressErrors.address}
                        <Field.Error>{addressErrors.address}</Field.Error>
                    {/if}
                </Field.Field>
            </Field.Group>

            <Dialog.Footer class="gap-2 sm:justify-start">
                <Button type="submit" class="h-10 rounded-xl px-5 font-black" disabled={addressProcessing}>
                    {#if addressProcessing}
                        <Spinner data-icon="inline-start" />
                    {/if}
                    ذخیره آدرس
                </Button>
                <Button type="button" variant="outline" class="h-10 rounded-xl px-5 font-black" onclick={closeAddressDialog} disabled={addressProcessing}>
                    انصراف
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
