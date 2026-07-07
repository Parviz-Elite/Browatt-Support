<script>
    import { router } from '@inertiajs/svelte';
    import { Building2, MessageSquareText, Plus, RefreshCcw, Save, Settings, ShieldCheck, Trash2 } from '@lucide/svelte';
    import { toast } from 'svelte-sonner';
    import DashboardShell from '@/Components/DashboardShell.svelte';
    import * as Alert from '@/Components/ui/alert';
    import { Badge } from '@/Components/ui/badge';
    import { Button } from '@/Components/ui/button';
    import * as Card from '@/Components/ui/card';
    import { Checkbox } from '@/Components/ui/checkbox';
    import * as Field from '@/Components/ui/field';
    import { Input } from '@/Components/ui/input';
    import { Spinner } from '@/Components/ui/spinner';
    import * as Tabs from '@/Components/ui/tabs';

    export let auth = { user: null };
    export let accounting = {
        enabled: true,
        base_url: '',
        financial_unit: '',
        username: '',
        timeout_seconds: 30,
        has_password: false,
        cities: {
            synced_at: null,
            synced_at_label: null,
            last_error: null,
            count: 0,
        },
    };
    export let warrantySettings = {
        detail_good_full_code_rules: [
            { prefix: '3001', good_full_code: '7001001' },
            { prefix: '3003', good_full_code: '7001002' },
            { prefix: '3004', good_full_code: '7001003' },
        ],
    };
    export let sms = {
        provider: 'farazsms',
        base_url: 'https://api.iranpayamak.com',
        line_number: '',
        otp_pattern_code: '',
        otp_attribute: 'code',
        number_format: 'english',
        timeout_seconds: 10,
        has_api_key: false,
    };
    export let otpSecurity = {
        code_length: 6,
        ttl_minutes: 2,
        max_attempts: 5,
        resend_seconds: 60,
        send_sms: false,
        store_debug_code: true,
        mobile_max_requests: 5,
        mobile_decay_seconds: 3600,
        ip_max_requests: 30,
        ip_decay_seconds: 3600,
        verify_mobile_max_attempts: 10,
        verify_mobile_decay_seconds: 600,
        verify_ip_max_attempts: 60,
        verify_ip_decay_seconds: 3600,
    };

    const activeTabs = ['accounting', 'sms', 'system'];
    const storedTab = typeof window !== 'undefined' ? window.localStorage.getItem('browatt.settings.active_tab') : null;
    let activeTab = activeTabs.includes(storedTab) ? storedTab : 'accounting';
    let accountingProcessing = false;
    let accountingErrors = {};
    let accountingForm = {
        enabled: Boolean(accounting.enabled),
        base_url: accounting.base_url ?? '',
        financial_unit: accounting.financial_unit ?? '',
        username: accounting.username ?? '',
        password: accounting.password ?? '',
        timeout_seconds: accounting.timeout_seconds ?? 30,
    };
    let citiesSyncProcessing = false;
    let warrantyRulesProcessing = false;
    let warrantyRulesErrors = {};
    let warrantyRulesForm = {
        rules: (warrantySettings.detail_good_full_code_rules ?? []).map((rule) => ({ ...rule })),
    };
    let smsProcessing = false;
    let smsErrors = {};
    let smsForm = {
        base_url: sms.base_url ?? 'https://api.iranpayamak.com',
        api_key: sms.api_key ?? '',
        line_number: sms.line_number ?? '',
        otp_pattern_code: sms.otp_pattern_code ?? '',
        otp_attribute: sms.otp_attribute ?? 'code',
        number_format: sms.number_format ?? 'english',
        timeout_seconds: sms.timeout_seconds ?? 10,
    };
    let securityProcessing = false;
    let securityErrors = {};
    let securityForm = {
        code_length: otpSecurity.code_length ?? 6,
        ttl_minutes: otpSecurity.ttl_minutes ?? 2,
        max_attempts: otpSecurity.max_attempts ?? 5,
        resend_seconds: otpSecurity.resend_seconds ?? 60,
        send_sms: Boolean(otpSecurity.send_sms),
        store_debug_code: Boolean(otpSecurity.store_debug_code),
        mobile_max_requests: otpSecurity.mobile_max_requests ?? 5,
        mobile_decay_seconds: otpSecurity.mobile_decay_seconds ?? 3600,
        ip_max_requests: otpSecurity.ip_max_requests ?? 30,
        ip_decay_seconds: otpSecurity.ip_decay_seconds ?? 3600,
        verify_mobile_max_attempts: otpSecurity.verify_mobile_max_attempts ?? 10,
        verify_mobile_decay_seconds: otpSecurity.verify_mobile_decay_seconds ?? 600,
        verify_ip_max_attempts: otpSecurity.verify_ip_max_attempts ?? 60,
        verify_ip_decay_seconds: otpSecurity.verify_ip_decay_seconds ?? 3600,
    };

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);

    $: if (typeof window !== 'undefined' && activeTabs.includes(activeTab)) {
        window.localStorage.setItem('browatt.settings.active_tab', activeTab);
    }

    function validationFeedback() {
        toast.error('ذخیره انجام نشد. لطفا خطاهای فرم را بررسی کنید.');
    }

    function saveAccounting() {
        accountingProcessing = true;
        accountingErrors = {};

        router.put(routeUrl('admin.settings.accounting.update', '/settings/accounting'), accountingForm, {
            preserveScroll: true,
            onError: (errors) => {
                accountingErrors = errors;
                validationFeedback();
            },
            onFinish: () => {
                accountingProcessing = false;
            },
        });
    }

    function syncCities() {
        citiesSyncProcessing = true;

        router.post(
            routeUrl('admin.settings.accounting.sync_cities', '/settings/accounting/sync-cities'),
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    citiesSyncProcessing = false;
                },
            },
        );
    }

    function addWarrantyRule() {
        warrantyRulesForm.rules = [...warrantyRulesForm.rules, { prefix: '', good_full_code: '' }];
    }

    function removeWarrantyRule(index) {
        warrantyRulesForm.rules = warrantyRulesForm.rules.filter((_, ruleIndex) => ruleIndex !== index);
    }

    function saveWarrantyRules() {
        warrantyRulesProcessing = true;
        warrantyRulesErrors = {};

        router.put(routeUrl('admin.settings.warranty_rules.update', '/settings/warranty-rules'), warrantyRulesForm, {
            preserveScroll: true,
            onError: (errors) => {
                warrantyRulesErrors = errors;
                validationFeedback();
            },
            onFinish: () => {
                warrantyRulesProcessing = false;
            },
        });
    }

    function saveSms() {
        smsProcessing = true;
        smsErrors = {};

        router.put(routeUrl('admin.settings.sms.update', '/settings/sms'), smsForm, {
            preserveScroll: true,
            onError: (errors) => {
                smsErrors = errors;
                validationFeedback();
            },
            onFinish: () => {
                smsProcessing = false;
            },
        });
    }

    function saveSecurity() {
        securityProcessing = true;
        securityErrors = {};

        router.put(routeUrl('admin.settings.otp_security.update', '/settings/otp-security'), securityForm, {
            preserveScroll: true,
            onError: (errors) => {
                securityErrors = errors;
                validationFeedback();
            },
            onFinish: () => {
                securityProcessing = false;
            },
        });
    }
</script>

<DashboardShell title="تنظیمات" {auth}>
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-5">
        <div class="rounded-2xl bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-950">تنظیمات سامانه</h2>
                    <p class="mt-1 text-sm font-bold leading-7 text-slate-500">هر بخش تنظیمات به صورت مستقل ذخیره می‌شود.</p>
                </div>
                <Badge variant="secondary" class="w-fit font-black">مدیریت</Badge>
            </div>
        </div>

        <Tabs.Root bind:value={activeTab} class="gap-5">
            <Tabs.List class="h-auto w-full justify-start overflow-x-auto rounded-2xl bg-white p-2 shadow-sm" variant="line">
                <Tabs.Trigger value="accounting" class={`h-11 min-w-fit gap-2 rounded-xl px-4 font-black ${activeTab === 'accounting' ? 'bg-red-50 text-slate-950 shadow-sm' : 'text-slate-600'}`}>
                    <Building2 data-icon="inline-start" />
                    اتصال حسابداری
                </Tabs.Trigger>
                <Tabs.Trigger value="sms" class={`h-11 min-w-fit gap-2 rounded-xl px-4 font-black ${activeTab === 'sms' ? 'bg-red-50 text-slate-950 shadow-sm' : 'text-slate-600'}`}>
                    <MessageSquareText data-icon="inline-start" />
                    پیامک
                </Tabs.Trigger>
                <Tabs.Trigger value="system" class={`h-11 min-w-fit gap-2 rounded-xl px-4 font-black ${activeTab === 'system' ? 'bg-red-50 text-slate-950 shadow-sm' : 'text-slate-600'}`}>
                    <Settings data-icon="inline-start" />
                    سامانه
                </Tabs.Trigger>
            </Tabs.List>

            <Tabs.Content value="accounting">
                <form
                    onsubmit={(event) => {
                        event.preventDefault();
                        saveAccounting();
                    }}
                >
                    <Card.Root class="rounded-2xl bg-white shadow-sm">
                        <Card.Header class="border-b">
                            <Card.Title class="flex items-center gap-2 text-lg font-black text-slate-950">
                                <ShieldCheck />
                                تنظیمات اتصال به حسابداری
                            </Card.Title>
                            <Card.Description>اطلاعات اتصال وب سرویس مهرسافت برای استعلام و ثبت گارانتی.</Card.Description>
                        </Card.Header>

                        <Card.Content>
                            <Field.Group>
                                <Field.Field orientation="horizontal">
                                    <Checkbox bind:checked={accountingForm.enabled} id="accounting-enabled" />
                                    <Field.Content>
                                        <Field.Label for="accounting-enabled">اتصال حسابداری فعال باشد</Field.Label>
                                    </Field.Content>
                                </Field.Field>

                                <Field.Field data-invalid={Boolean(accountingErrors.base_url)}>
                                    <Field.Label for="accounting-base-url">آدرس وب سرویس حسابداری</Field.Label>
                                    <Input
                                        id="accounting-base-url"
                                        bind:value={accountingForm.base_url}
                                        dir="ltr"
                                        placeholder="https://www.mehrsofts.com/webservice/mehraccws.asmx"
                                        aria-invalid={Boolean(accountingErrors.base_url)}
                                    />
                                    {#if accountingErrors.base_url}
                                        <Field.Error>{accountingErrors.base_url}</Field.Error>
                                    {/if}
                                </Field.Field>

                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <Field.Field data-invalid={Boolean(accountingErrors.financial_unit)}>
                                        <Field.Label for="accounting-financial-unit">کد سال مالی</Field.Label>
                                        <Input
                                            id="accounting-financial-unit"
                                            bind:value={accountingForm.financial_unit}
                                            type="number"
                                            min="1"
                                            dir="ltr"
                                            aria-invalid={Boolean(accountingErrors.financial_unit)}
                                        />
                                        {#if accountingErrors.financial_unit}
                                            <Field.Error>{accountingErrors.financial_unit}</Field.Error>
                                        {/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(accountingErrors.username)}>
                                        <Field.Label for="accounting-username">نام کاربری</Field.Label>
                                        <Input id="accounting-username" bind:value={accountingForm.username} dir="ltr" autocomplete="off" aria-invalid={Boolean(accountingErrors.username)} />
                                        {#if accountingErrors.username}
                                            <Field.Error>{accountingErrors.username}</Field.Error>
                                        {/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(accountingErrors.password)}>
                                        <Field.Label for="accounting-password">رمز عبور</Field.Label>
                                        <Input
                                            id="accounting-password"
                                            bind:value={accountingForm.password}
                                            type="password"
                                            dir="ltr"
                                            autocomplete="new-password"
                                            placeholder={accounting.has_password ? 'بدون تغییر' : ''}
                                            aria-invalid={Boolean(accountingErrors.password)}
                                        />
                                        {#if accountingErrors.password}
                                            <Field.Error>{accountingErrors.password}</Field.Error>
                                        {/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(accountingErrors.timeout_seconds)}>
                                        <Field.Label for="accounting-timeout">زمان انتظار</Field.Label>
                                        <Input
                                            id="accounting-timeout"
                                            bind:value={accountingForm.timeout_seconds}
                                            type="number"
                                            min="5"
                                            max="120"
                                            dir="ltr"
                                            aria-invalid={Boolean(accountingErrors.timeout_seconds)}
                                        />
                                        {#if accountingErrors.timeout_seconds}
                                            <Field.Error>{accountingErrors.timeout_seconds}</Field.Error>
                                        {/if}
                                    </Field.Field>
                                </div>
                            </Field.Group>
                        </Card.Content>

                        <Card.Footer class="justify-end">
                            <Button type="submit" disabled={accountingProcessing} class="h-11 gap-2 rounded-xl !px-5 font-black">
                                {#if accountingProcessing}
                                    <Spinner data-icon="inline-start" />
                                {:else}
                                    <Save data-icon="inline-start" />
                                {/if}
                                ذخیره اتصال حسابداری
                            </Button>
                        </Card.Footer>
                    </Card.Root>
                </form>

                <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <Card.Root class="rounded-2xl bg-white shadow-sm">
                        <Card.Header class="border-b">
                            <Card.Title class="text-lg font-black text-slate-950">استان و شهر حسابداری</Card.Title>
                            <Card.Description>داده‌های استان و شهر از MehrSoft خوانده و در تنظیمات سامانه ذخیره می‌شود.</Card.Description>
                        </Card.Header>
                        <Card.Content>
                            <div class="grid gap-3 text-sm font-bold leading-7 text-slate-600">
                                <div>تعداد رکوردها: <span class="font-black text-slate-950">{accounting.cities?.count ?? 0}</span></div>
                                <div>آخرین همگام‌سازی: <span class="font-black text-slate-950">{accounting.cities?.synced_at_label ?? 'انجام نشده'}</span></div>
                                {#if accounting.cities?.last_error}
                                    <Alert.Root variant="destructive">
                                        <Alert.Description>{accounting.cities.last_error}</Alert.Description>
                                    </Alert.Root>
                                {/if}
                            </div>
                        </Card.Content>
                        <Card.Footer class="justify-end">
                            <Button type="button" disabled={citiesSyncProcessing} class="h-11 gap-2 rounded-xl !px-5 font-black" onclick={syncCities}>
                                {#if citiesSyncProcessing}
                                    <Spinner data-icon="inline-start" />
                                {:else}
                                    <RefreshCcw data-icon="inline-start" />
                                {/if}
                                همگام‌سازی استان و شهر
                            </Button>
                        </Card.Footer>
                    </Card.Root>

                    <form
                        onsubmit={(event) => {
                            event.preventDefault();
                            saveWarrantyRules();
                        }}
                    >
                        <Card.Root class="rounded-2xl bg-white shadow-sm">
                            <Card.Header class="border-b">
                                <Card.Title class="text-lg font-black text-slate-950">قوانین کد خدمات گارانتی</Card.Title>
                                <Card.Description>کد خدمات ارسال‌شده در Details براساس پیشوند کد کالای دریافتی از حسابداری تعیین می‌شود.</Card.Description>
                            </Card.Header>
                            <Card.Content>
                                <Field.Group>
                                    {#each warrantyRulesForm.rules as rule, index}
                                        <div class="grid gap-3 rounded-2xl border border-slate-200 p-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                                            <Field.Field data-invalid={Boolean(warrantyRulesErrors[`rules.${index}.prefix`])}>
                                                <Field.Label for={`rule-prefix-${index}`}>پیشوند کد کالا</Field.Label>
                                                <Input id={`rule-prefix-${index}`} bind:value={rule.prefix} dir="ltr" aria-invalid={Boolean(warrantyRulesErrors[`rules.${index}.prefix`])} />
                                                {#if warrantyRulesErrors[`rules.${index}.prefix`]}<Field.Error>{warrantyRulesErrors[`rules.${index}.prefix`]}</Field.Error>{/if}
                                            </Field.Field>

                                            <Field.Field data-invalid={Boolean(warrantyRulesErrors[`rules.${index}.good_full_code`])}>
                                                <Field.Label for={`rule-code-${index}`}>کد خدمات</Field.Label>
                                                <Input id={`rule-code-${index}`} bind:value={rule.good_full_code} dir="ltr" aria-invalid={Boolean(warrantyRulesErrors[`rules.${index}.good_full_code`])} />
                                                {#if warrantyRulesErrors[`rules.${index}.good_full_code`]}<Field.Error>{warrantyRulesErrors[`rules.${index}.good_full_code`]}</Field.Error>{/if}
                                            </Field.Field>

                                            <div class="flex items-end">
                                                <Button type="button" variant="outline" size="icon" class="rounded-xl" onclick={() => removeWarrantyRule(index)} disabled={warrantyRulesForm.rules.length === 1}>
                                                    <Trash2 />
                                                </Button>
                                            </div>
                                        </div>
                                    {/each}

                                    {#if warrantyRulesErrors.rules}<Field.Error>{warrantyRulesErrors.rules}</Field.Error>{/if}

                                    <Button type="button" variant="outline" class="w-fit rounded-xl font-black" onclick={addWarrantyRule}>
                                        <Plus data-icon="inline-start" />
                                        افزودن قانون
                                    </Button>
                                </Field.Group>
                            </Card.Content>
                            <Card.Footer class="justify-end">
                                <Button type="submit" disabled={warrantyRulesProcessing} class="h-11 gap-2 rounded-xl !px-5 font-black">
                                    {#if warrantyRulesProcessing}
                                        <Spinner data-icon="inline-start" />
                                    {:else}
                                        <Save data-icon="inline-start" />
                                    {/if}
                                    ذخیره قوانین گارانتی
                                </Button>
                            </Card.Footer>
                        </Card.Root>
                    </form>
                </div>
            </Tabs.Content>

            <Tabs.Content value="sms">
                <form
                    onsubmit={(event) => {
                        event.preventDefault();
                        saveSms();
                    }}
                >
                    <Card.Root class="rounded-2xl bg-white shadow-sm">
                        <Card.Header class="border-b">
                            <Card.Title class="text-lg font-black text-slate-950">تنظیمات پیامک فراز اس ام اس</Card.Title>
                            <Card.Description>ارسال OTP با endpoint پترن انجام می‌شود و احراز هویت با کلید API در هدر است.</Card.Description>
                        </Card.Header>
                        <Card.Content>
                            <Field.Group>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm font-bold leading-7 text-slate-600">
                                    فراز اس ام اس برای این وب سرویس نام کاربری و رمز عبور نمی‌گیرد؛ کلید API، کد پترن، شماره خط و نام متغیر پترن لازم است.
                                </div>

                                <div class="grid gap-4 lg:grid-cols-2">
                                    <Field.Field data-invalid={Boolean(smsErrors.base_url)}>
                                        <Field.Label for="sms-base-url">آدرس پایه API</Field.Label>
                                        <Input id="sms-base-url" bind:value={smsForm.base_url} dir="ltr" aria-invalid={Boolean(smsErrors.base_url)} />
                                        {#if smsErrors.base_url}<Field.Error>{smsErrors.base_url}</Field.Error>{/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(smsErrors.api_key)}>
                                        <Field.Label for="sms-api-key">کلید API</Field.Label>
                                        <Input id="sms-api-key" bind:value={smsForm.api_key} type="password" dir="ltr" autocomplete="new-password" placeholder={sms.has_api_key ? 'بدون تغییر' : ''} aria-invalid={Boolean(smsErrors.api_key)} />
                                        {#if smsErrors.api_key}<Field.Error>{smsErrors.api_key}</Field.Error>{/if}
                                    </Field.Field>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <Field.Field data-invalid={Boolean(smsErrors.line_number)}>
                                        <Field.Label for="sms-line-number">شماره خط ارسال کننده</Field.Label>
                                        <Input id="sms-line-number" bind:value={smsForm.line_number} dir="ltr" aria-invalid={Boolean(smsErrors.line_number)} />
                                        {#if smsErrors.line_number}<Field.Error>{smsErrors.line_number}</Field.Error>{/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(smsErrors.otp_pattern_code)}>
                                        <Field.Label for="sms-pattern-code">کد پترن ورود</Field.Label>
                                        <Input id="sms-pattern-code" bind:value={smsForm.otp_pattern_code} dir="ltr" aria-invalid={Boolean(smsErrors.otp_pattern_code)} />
                                        {#if smsErrors.otp_pattern_code}<Field.Error>{smsErrors.otp_pattern_code}</Field.Error>{/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(smsErrors.otp_attribute)}>
                                        <Field.Label for="sms-otp-attribute">نام متغیر کد</Field.Label>
                                        <Input id="sms-otp-attribute" bind:value={smsForm.otp_attribute} dir="ltr" placeholder="code" aria-invalid={Boolean(smsErrors.otp_attribute)} />
                                        {#if smsErrors.otp_attribute}<Field.Error>{smsErrors.otp_attribute}</Field.Error>{/if}
                                    </Field.Field>

                                    <Field.Field data-invalid={Boolean(smsErrors.timeout_seconds)}>
                                        <Field.Label for="sms-timeout">زمان انتظار</Field.Label>
                                        <Input id="sms-timeout" bind:value={smsForm.timeout_seconds} type="number" min="3" max="60" dir="ltr" aria-invalid={Boolean(smsErrors.timeout_seconds)} />
                                        {#if smsErrors.timeout_seconds}<Field.Error>{smsErrors.timeout_seconds}</Field.Error>{/if}
                                    </Field.Field>
                                </div>

                                <Field.Set>
                                    <Field.Legend>فرمت عدد در پیامک</Field.Legend>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <Button type="button" variant={smsForm.number_format === 'english' ? 'default' : 'outline'} class="rounded-xl font-black" onclick={() => (smsForm.number_format = 'english')}>انگلیسی</Button>
                                        <Button type="button" variant={smsForm.number_format === 'persian' ? 'default' : 'outline'} class="rounded-xl font-black" onclick={() => (smsForm.number_format = 'persian')}>فارسی</Button>
                                    </div>
                                    {#if smsErrors.number_format}<Field.Error>{smsErrors.number_format}</Field.Error>{/if}
                                </Field.Set>
                            </Field.Group>
                        </Card.Content>
                        <Card.Footer class="justify-end">
                            <Button type="submit" disabled={smsProcessing} class="h-11 gap-2 rounded-xl !px-5 font-black">
                                {#if smsProcessing}<Spinner data-icon="inline-start" />{:else}<Save data-icon="inline-start" />{/if}
                                ذخیره پیامک
                            </Button>
                        </Card.Footer>
                    </Card.Root>
                </form>
            </Tabs.Content>

            <Tabs.Content value="system">
                <form
                    onsubmit={(event) => {
                        event.preventDefault();
                        saveSecurity();
                    }}
                >
                    <Card.Root class="rounded-2xl bg-white shadow-sm">
                        <Card.Header class="border-b">
                            <Card.Title class="text-lg font-black text-slate-950">تنظیمات امنیتی ورود پیامکی</Card.Title>
                            <Card.Description>مدت اعتبار کد، ارسال مجدد، تعداد تلاش و محدودیت‌های موبایل و IP.</Card.Description>
                        </Card.Header>
                        <Card.Content>
                            <Field.Group>
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    {#each [
                                        ['code_length', 'طول کد ورود', 4, 8],
                                        ['ttl_minutes', 'مدت اعتبار کد', 1, 10],
                                        ['max_attempts', 'تلاش مجاز تایید هر کد', 1, 10],
                                        ['resend_seconds', 'فاصله ارسال مجدد', 30, 600],
                                    ] as item}
                                        <Field.Field data-invalid={Boolean(securityErrors[item[0]])}>
                                            <Field.Label for={`security-${item[0]}`}>{item[1]}</Field.Label>
                                            <Input id={`security-${item[0]}`} bind:value={securityForm[item[0]]} type="number" min={item[2]} max={item[3]} dir="ltr" aria-invalid={Boolean(securityErrors[item[0]])} />
                                            {#if securityErrors[item[0]]}<Field.Error>{securityErrors[item[0]]}</Field.Error>{/if}
                                        </Field.Field>
                                    {/each}
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <Field.Field orientation="horizontal">
                                        <Checkbox bind:checked={securityForm.send_sms} id="security-send-sms" />
                                        <Field.Content>
                                            <Field.Label for="security-send-sms">ارسال پیامک واقعی فعال باشد</Field.Label>
                                        </Field.Content>
                                    </Field.Field>

                                    <Field.Field orientation="horizontal">
                                        <Checkbox bind:checked={securityForm.store_debug_code} id="security-debug-code" />
                                        <Field.Content>
                                            <Field.Label for="security-debug-code">ذخیره کد تستی در محیط توسعه</Field.Label>
                                        </Field.Content>
                                    </Field.Field>
                                </div>

                                <Field.Set>
                                    <Field.Legend>محدودیت درخواست کد</Field.Legend>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        {#each [
                                            ['mobile_max_requests', 'سقف موبایل'],
                                            ['mobile_decay_seconds', 'بازه موبایل'],
                                            ['ip_max_requests', 'سقف IP'],
                                            ['ip_decay_seconds', 'بازه IP'],
                                        ] as item}
                                            <Field.Field data-invalid={Boolean(securityErrors[item[0]])}>
                                                <Field.Label for={`security-${item[0]}`}>{item[1]}</Field.Label>
                                                <Input id={`security-${item[0]}`} bind:value={securityForm[item[0]]} type="number" min="1" dir="ltr" aria-invalid={Boolean(securityErrors[item[0]])} />
                                                {#if securityErrors[item[0]]}<Field.Error>{securityErrors[item[0]]}</Field.Error>{/if}
                                            </Field.Field>
                                        {/each}
                                    </div>
                                </Field.Set>

                                <Field.Set>
                                    <Field.Legend>محدودیت تایید کد</Field.Legend>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        {#each [
                                            ['verify_mobile_max_attempts', 'سقف موبایل'],
                                            ['verify_mobile_decay_seconds', 'بازه موبایل'],
                                            ['verify_ip_max_attempts', 'سقف IP'],
                                            ['verify_ip_decay_seconds', 'بازه IP'],
                                        ] as item}
                                            <Field.Field data-invalid={Boolean(securityErrors[item[0]])}>
                                                <Field.Label for={`security-${item[0]}`}>{item[1]}</Field.Label>
                                                <Input id={`security-${item[0]}`} bind:value={securityForm[item[0]]} type="number" min="1" dir="ltr" aria-invalid={Boolean(securityErrors[item[0]])} />
                                                {#if securityErrors[item[0]]}<Field.Error>{securityErrors[item[0]]}</Field.Error>{/if}
                                            </Field.Field>
                                        {/each}
                                    </div>
                                </Field.Set>
                            </Field.Group>
                        </Card.Content>
                        <Card.Footer class="justify-end">
                            <Button type="submit" disabled={securityProcessing} class="h-11 gap-2 rounded-xl !px-5 font-black">
                                {#if securityProcessing}<Spinner data-icon="inline-start" />{:else}<Save data-icon="inline-start" />{/if}
                                ذخیره تنظیمات امنیتی
                            </Button>
                        </Card.Footer>
                    </Card.Root>
                </form>
            </Tabs.Content>
        </Tabs.Root>
    </div>
</DashboardShell>
