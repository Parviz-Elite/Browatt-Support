<script>
    import axios from 'axios';
    import { router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import { ArrowLeft, CheckCircle2, ClipboardCheck, Hash, Info, PackageSearch, ScanLine, ShieldPlus } from '@lucide/svelte';
    import BarcodeScanner from '@/Components/BarcodeScanner.svelte';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import { Button } from '@/Components/ui/button';
    import * as Dialog from '@/Components/ui/dialog';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import { Separator } from '@/Components/ui/separator';
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
        state_code: '',
        city_code: '',
    };
    export let cities = [];
    export let addresses = [];
    export let activationSupport = { phone: null, href: null };

    let serial = '';
    let barcodeScannerOpen = false;
    let productChecked = false;
    let productChecking = false;
    let processing = false;
    let inquiryError = null;
    let inquiryPhone = null;
    let errors = {};
    let product = null;
    let warrantyId = null;
    let successDialogOpen = false;

    const defaultAddress = addresses.find((address) => address.is_default) ?? addresses[0] ?? null;
    let selectedAddressId = defaultAddress?.id ? String(defaultAddress.id) : 'new';

    function cityByCode(code) {
        return cities.find((item) => String(item.code) === String(code));
    }

    function initialStateCode() {
        if (profile.state_code) {
            const state = cityByCode(profile.state_code);

            if (state && !state.parent_code) {
                return String(state.code);
            }

            if (state?.parent_code) {
                return String(state.parent_code);
            }
        }

        if (profile.city_code) {
            const city = cityByCode(profile.city_code);

            if (city?.parent_code) {
                return String(city.parent_code);
            }
        }

        return cities.find((item) => !item.parent_code && item.name === defaultAddress?.province)?.code ?? '';
    }

    function initialCityCode(stateCode) {
        if (profile.city_code) {
            return String(profile.city_code);
        }

        return cities.find((item) => item.parent_code === stateCode && item.name === defaultAddress?.city)?.code ?? '';
    }

    function stateCodeForAddress(address) {
        return cities.find((item) => !item.parent_code && item.name === address?.province)?.code ?? '';
    }

    function cityCodeForAddress(address, stateCode) {
        return cities.find((item) => item.parent_code === stateCode && item.name === address?.city)?.code ?? '';
    }

    function addressLabel(address) {
        const location = [address.province, address.city].filter(Boolean).join('، ');
        const title = address.title || location || address.address;

        if (!title) {
            return 'آدرس بدون عنوان';
        }

        return title === address.address ? title : `${title} - ${address.address || location}`;
    }

    function selectAddress() {
        if (selectedAddressId === 'new') {
            customerForm.address_title = '';
            customerForm.state_code = '';
            customerForm.city_code = '';
            customerForm.address = '';
            return;
        }

        const address = addresses.find((item) => String(item.id) === selectedAddressId);

        if (!address) {
            return;
        }

        const stateCode = stateCodeForAddress(address);

        customerForm.address_title = address.title ?? '';
        customerForm.state_code = stateCode;
        customerForm.city_code = cityCodeForAddress(address, stateCode);
        customerForm.address = address.address ?? '';
    }

    const initialState = initialStateCode();

    let customerForm = {
        first_name: profile.first_name ?? '',
        last_name: profile.last_name ?? '',
        cust_type: String(profile.cust_type ?? '0'),
        cust_sex: String(profile.cust_sex ?? '1'),
        cust_name: profile.cust_name ?? '',
        national_code: profile.national_code ?? '',
        address_title: defaultAddress?.title ?? '',
        state_code: initialState,
        city_code: initialCityCode(initialState),
        address: defaultAddress?.address ?? '',
    };

    $: states = cities.filter((item) => !item.parent_code);
    $: cityOptions = cities.filter((item) => item.parent_code === customerForm.state_code);
    $: nationalCodeValid = !customerForm.national_code || isValidNationalCode(customerForm.national_code);
    $: canCheckProduct = serial.trim().length >= 2;
    $: canSubmit =
        productChecked &&
        warrantyId &&
        customerForm.first_name.trim() &&
        customerForm.last_name.trim() &&
        customerForm.national_code.trim() &&
        nationalCodeValid &&
        customerForm.state_code &&
        customerForm.city_code &&
        customerForm.address.trim() &&
        (customerForm.cust_type !== '1' || customerForm.cust_name.trim());

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    const cardMotion = {
        hidden: { opacity: 0, y: 18, scale: 0.985 },
        show: (i) => ({
            opacity: 1,
            y: 0,
            scale: 1,
            transition: { delay: i * 0.06, duration: 0.34, ease: 'easeOut' },
        }),
    };

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

    async function checkProduct() {
        if (!canCheckProduct) {
            return;
        }

        productChecking = true;
        productChecked = false;
        inquiryError = null;
        inquiryPhone = null;
        errors = {};
        product = null;
        warrantyId = null;

        try {
            const response = await axios.post(routeUrl('warranties.product_inquiry', '/warranties/product-inquiry'), {
                serial,
            });

            product = response.data.product;
            warrantyId = response.data.warranty_id;
            productChecked = true;
        } catch (error) {
            const data = error.response?.data ?? {};
            const message = data.message ?? data.errors?.serial?.[0] ?? 'استعلام محصول انجام نشد.';

            inquiryError = message;
            inquiryPhone = data.phone_href ? { href: data.phone_href, label: data.phone ?? '02191693797' } : null;
            errors = { serial: message };
        } finally {
            productChecking = false;
        }
    }

    function submitActivation() {
        if (!canSubmit) {
            return;
        }

        processing = true;
        errors = {};

        router.post(
            routeUrl('warranties.activate.store', '/warranties/activate'),
            {
                warranty_id: warrantyId,
                first_name: customerForm.first_name,
                last_name: customerForm.last_name,
                cust_type: customerForm.cust_type,
                cust_sex: customerForm.cust_sex,
                cust_name: customerForm.cust_name,
                national_code: customerForm.national_code,
                address_id: selectedAddressId === 'new' ? null : selectedAddressId,
                address_title: customerForm.address_title,
                state_code: customerForm.state_code,
                city_code: customerForm.city_code,
                address: customerForm.address,
                show_success_dialog: true,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    successDialogOpen = true;
                },
                onError: (pageErrors) => {
                    errors = pageErrors;
                },
                onFinish: () => {
                    processing = false;
                },
            },
        );
    }

    function resetInquiry() {
        productChecked = false;
        product = null;
        warrantyId = null;
    }
</script>

<DashboardShell title="فعال سازی گارانتی" {auth}>
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]">
        <div class="flex flex-col gap-5">
            <motion.form
                class="rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)] sm:p-6"
                variants={cardMotion}
                initial="hidden"
                animate="show"
                custom={0}
                onsubmit={(event) => {
                    event.preventDefault();
                    checkProduct();
                }}
            >
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-sm font-black text-sky-700">
                            <ShieldPlus size={16} />
                            مرحله اول
                        </div>
                        <h2 class="mt-4 text-2xl font-black leading-9 text-slate-950">استعلام محصول</h2>
                    </div>

                    <div class="hidden size-14 items-center justify-center rounded-3xl bg-[#17275d] text-white sm:flex">
                        <ClipboardCheck size={26} />
                    </div>
                </div>

                <Field.Group>
                    <Field.Field data-invalid={Boolean(errors.serial)}>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <Field.Label for="product-serial">شماره سریال محصول</Field.Label>
                            <Button type="button" size="sm" class="rounded-xl font-black" onclick={() => (barcodeScannerOpen = true)}>
                                <ScanLine data-icon="inline-start" />
                                اسکن بارکد
                            </Button>
                        </div>
                        <div class="relative">
                            <Hash class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={19} />
                            <Input
                                id="product-serial"
                                bind:value={serial}
                                dir="ltr"
                                autocomplete="off"
                                class="h-14 pr-12 text-left text-base font-black"
                                placeholder="Serial / SN"
                                disabled={productChecking || productChecked}
                                oninput={resetInquiry}
                            />
                        </div>
                        {#if inquiryError}
                            <Field.Error>
                                {inquiryError}
                                {#if inquiryPhone}
                                    <span class="block">
                                        تماس سریع:
                                        <a class="font-black underline" href={inquiryPhone.href}>{inquiryPhone.label}</a>
                                    </span>
                                {/if}
                            </Field.Error>
                        {/if}
                    </Field.Field>
                </Field.Group>

                {#if product}
                    <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold leading-7 text-emerald-800">
                        <div class="flex flex-col gap-1">
                            <span>عنوان کالا: {product.name || 'نام کالا ثبت نشده'}</span>
                        </div>
                    </div>
                {:else}
                    <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm font-bold leading-7 text-sky-800">
                        <div class="flex items-start gap-2">
                            <Info class="mt-1 shrink-0" size={17} />
                            <span>ابتدا سریال محصول بررسی می‌شود و بعد فرم فعال‌سازی باز خواهد شد.</span>
                        </div>
                    </div>
                {/if}

                <div class="mt-6 flex justify-end">
                    <Button type="submit" disabled={!canCheckProduct || productChecking || productChecked} class="h-14 gap-3 rounded-2xl !px-7 text-base font-black">
                        {#if productChecking}
                            <Spinner data-icon="inline-start" />
                        {/if}
                        استعلام محصول
                        <ArrowLeft data-icon="inline-end" />
                    </Button>
                </div>
            </motion.form>

            {#if productChecked}
                <motion.form
                    class="rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)] sm:p-6"
                    variants={cardMotion}
                    initial="hidden"
                    animate="show"
                    custom={1}
                    onsubmit={(event) => {
                        event.preventDefault();
                        submitActivation();
                    }}
                >
                    <div class="mb-6">
                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-black text-emerald-700">
                            <CheckCircle2 size={16} />
                            مرحله دوم
                        </div>
                        <h2 class="mt-4 text-2xl font-black leading-9 text-slate-950">مشخصات خریدار و فعال‌سازی</h2>
                    </div>

                    <Field.Group>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field.Field data-invalid={Boolean(errors.cust_type)}>
                                <Field.Label for="cust-type">نوع مشتری</Field.Label>
                                <select id="cust-type" bind:value={customerForm.cust_type} class="h-11 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none">
                                    <option value="0">حقیقی</option>
                                    <option value="1">حقوقی</option>
                                </select>
                                {#if errors.cust_type}<Field.Error>{errors.cust_type}</Field.Error>{/if}
                            </Field.Field>

                            <Field.Field data-invalid={Boolean(errors.cust_sex)}>
                                <Field.Label for="cust-sex">جنسیت</Field.Label>
                                <select id="cust-sex" bind:value={customerForm.cust_sex} class="h-11 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none">
                                    <option value="0">خانم</option>
                                    <option value="1">آقا</option>
                                </select>
                                {#if errors.cust_sex}<Field.Error>{errors.cust_sex}</Field.Error>{/if}
                            </Field.Field>
                        </div>

                        {#if customerForm.cust_type === '1'}
                            <Field.Field data-invalid={Boolean(errors.cust_name)}>
                                <Field.Label for="cust-name">نام شخص حقوقی</Field.Label>
                                <Input id="cust-name" bind:value={customerForm.cust_name} aria-invalid={Boolean(errors.cust_name)} />
                                {#if errors.cust_name}<Field.Error>{errors.cust_name}</Field.Error>{/if}
                            </Field.Field>
                        {/if}

                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field.Field data-invalid={Boolean(errors.first_name)}>
                                <Field.Label for="first-name">نام</Field.Label>
                                <Input id="first-name" bind:value={customerForm.first_name} aria-invalid={Boolean(errors.first_name)} />
                                {#if errors.first_name}<Field.Error>{errors.first_name}</Field.Error>{/if}
                            </Field.Field>

                            <Field.Field data-invalid={Boolean(errors.last_name)}>
                                <Field.Label for="last-name">نام خانوادگی</Field.Label>
                                <Input id="last-name" bind:value={customerForm.last_name} aria-invalid={Boolean(errors.last_name)} />
                                {#if errors.last_name}<Field.Error>{errors.last_name}</Field.Error>{/if}
                            </Field.Field>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <Field.Field data-invalid={Boolean(errors.national_code) || !nationalCodeValid}>
                                <Field.Label for="national-code">کد ملی</Field.Label>
                                <Input id="national-code" bind:value={customerForm.national_code} dir="ltr" aria-invalid={Boolean(errors.national_code) || !nationalCodeValid} maxlength="10" />
                                {#if errors.national_code}
                                    <Field.Error>{errors.national_code}</Field.Error>
                                {:else if !nationalCodeValid}
                                    <Field.Error>کد ملی وارد شده معتبر نیست.</Field.Error>
                                {/if}
                            </Field.Field>

                            <Field.Field data-disabled>
                                <Field.Label for="mobile">شماره موبایل</Field.Label>
                                <Input id="mobile" value={profile.mobile ?? auth?.user?.mobile ?? ''} dir="ltr" disabled />
                            </Field.Field>
                        </div>
                    </Field.Group>

                    <Separator class="my-6" />

                    <Field.Set>
                        <Field.Legend>آدرس نصب / مصرف کننده</Field.Legend>

                        {#if !cities.length}
                            <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-7 text-amber-800">
                                لیست استان و شهر هنوز آماده نیست. مدیر سیستم باید اطلاعات استان و شهر را از بخش تنظیمات به‌روز کند.
                            </div>
                        {/if}

                        {#if addresses.length}
                            <Field.Field class="mt-4">
                                <Field.Label for="saved-address">انتخاب آدرس</Field.Label>
                                <select
                                    id="saved-address"
                                    bind:value={selectedAddressId}
                                    class="h-11 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none"
                                    onchange={selectAddress}
                                >
                                    {#each addresses as address (address.id)}
                                        <option value={String(address.id)}>
                                            {addressLabel(address)}
                                        </option>
                                    {/each}
                                    <option value="new">آدرس جدید</option>
                                </select>
                            </Field.Field>
                        {/if}

                        {#if selectedAddressId !== 'new'}
                            <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold leading-7 text-slate-700">
                                <div>{customerForm.address_title || 'آدرس انتخاب شده'}</div>
                                <div class="text-slate-500">{[cityByCode(customerForm.state_code)?.name, cityByCode(customerForm.city_code)?.name].filter(Boolean).join('، ')}</div>
                                <div class="text-slate-600">{customerForm.address}</div>
                            </div>
                        {:else}
                            <Field.Field class="mt-4" data-invalid={Boolean(errors.address_title)}>
                                <Field.Label for="address-title">عنوان آدرس</Field.Label>
                                <Input id="address-title" bind:value={customerForm.address_title} placeholder="مثال: خانه، دفتر و..." aria-invalid={Boolean(errors.address_title)} />
                                {#if errors.address_title}<Field.Error>{errors.address_title}</Field.Error>{/if}
                            </Field.Field>

                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <Field.Field data-invalid={Boolean(errors.state_code)}>
                                    <Field.Label for="state-code">استان</Field.Label>
                                    <select
                                        id="state-code"
                                        bind:value={customerForm.state_code}
                                        class="h-11 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none"
                                        onchange={() => (customerForm.city_code = '')}
                                    >
                                        <option value="">انتخاب استان</option>
                                        {#each states as state (state.code)}
                                            <option value={state.code}>{state.name}</option>
                                        {/each}
                                    </select>
                                    {#if errors.state_code}<Field.Error>{errors.state_code}</Field.Error>{/if}
                                </Field.Field>

                                <Field.Field data-invalid={Boolean(errors.city_code)}>
                                    <Field.Label for="city-code">شهر</Field.Label>
                                    <select id="city-code" bind:value={customerForm.city_code} class="h-11 rounded-xl border border-input bg-background px-3 text-sm font-bold shadow-xs outline-none" disabled={!customerForm.state_code}>
                                        <option value="">انتخاب شهر</option>
                                        {#each cityOptions as city (city.code)}
                                            <option value={city.code}>{city.name}</option>
                                        {/each}
                                    </select>
                                    {#if errors.city_code}<Field.Error>{errors.city_code}</Field.Error>{/if}
                                </Field.Field>
                            </div>

                            <Field.Field class="mt-4" data-invalid={Boolean(errors.address)}>
                                <Field.Label for="address">آدرس کامل</Field.Label>
                                <Textarea id="address" rows="4" bind:value={customerForm.address} aria-invalid={Boolean(errors.address)} />
                                {#if errors.address}<Field.Error>{errors.address}</Field.Error>{/if}
                            </Field.Field>
                        {/if}
                    </Field.Set>

                    {#if errors.activation}
                        <div class="mt-5 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-bold leading-7 text-red-700">
                            <div>{errors.activation}</div>
                            {#if activationSupport?.href}
                                <a class="inline-block font-black underline" href={activationSupport.href}>
                                    تماس سریع: {activationSupport.phone ?? '02191693797'}
                                </a>
                            {/if}
                        </div>
                    {/if}

                    <div class="mt-6 flex justify-end">
                        <Button type="submit" disabled={!canSubmit || processing} class="h-14 rounded-2xl px-6 text-base font-black">
                            {#if processing}
                                <Spinner data-icon="inline-start" />
                            {/if}
                            ثبت و فعال‌سازی گارانتی
                        </Button>
                    </div>
                </motion.form>
            {/if}
        </div>

        <div class="grid gap-5">
            <motion.div class="rounded-3xl bg-[#17275d] p-5 text-white shadow-[0_18px_60px_rgba(23,39,93,0.16)]" variants={cardMotion} initial="hidden" animate="show" custom={2}>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-black text-white/64">وضعیت ثبت</div>
                        <div class="mt-2 text-2xl font-black">{productChecked ? 'در انتظار تکمیل فرم' : 'در انتظار استعلام'}</div>
                    </div>
                    <PackageSearch size={34} />
                </div>
            </motion.div>

            <motion.div class="rounded-3xl border border-white/80 bg-white p-5 shadow-[0_18px_60px_rgba(23,39,93,0.08)]" variants={cardMotion} initial="hidden" animate="show" custom={3}>
                <div class="mb-4 flex items-center gap-2 text-sm font-black text-slate-500">
                    <ScanLine size={17} />
                    مراحل فعال‌سازی
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CheckCircle2 class={productChecked ? 'text-emerald-600' : 'text-sky-600'} size={19} />
                        <span class="text-sm font-black text-slate-700">استعلام محصول</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CheckCircle2 class={productChecked ? 'text-sky-600' : 'text-slate-300'} size={19} />
                        <span class="text-sm font-black text-slate-700">مشخصات خریدار</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                        <CheckCircle2 class="text-slate-300" size={19} />
                        <span class="text-sm font-black text-slate-500">ثبت نهایی گارانتی</span>
                    </div>
                </div>
            </motion.div>
        </div>
    </section>

    <BarcodeScanner bind:open={barcodeScannerOpen} onScan={(value) => (serial = value)} />

    <Dialog.Root bind:open={successDialogOpen}>
        <Dialog.Content class="max-w-[calc(100%-2rem)] rounded-3xl p-6 sm:max-w-md" showCloseButton={false}>
            <Dialog.Header>
                <div class="mx-auto mb-2 flex size-14 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-600">
                    <CheckCircle2 size={28} />
                </div>
                <Dialog.Title class="text-center text-xl font-black text-slate-950">گارانتی با موفقیت فعال شد</Dialog.Title>
                <Dialog.Description class="text-center leading-7 text-slate-600">
                    اطلاعات گارانتی ثبت شد. برای مشاهده جزئیات، به صفحه گارانتی های من بروید.
                </Dialog.Description>
            </Dialog.Header>
            <Dialog.Footer class="sm:justify-center">
                <Button
                    type="button"
                    class="h-12 w-full rounded-2xl font-black sm:w-auto"
                    onclick={() => router.visit(routeUrl('warranties.mine', '/warranties/mine'))}
                >
                    مشاهده گارانتی های من
                    <ArrowLeft data-icon="inline-end" />
                </Button>
            </Dialog.Footer>
        </Dialog.Content>
    </Dialog.Root>
</DashboardShell>
