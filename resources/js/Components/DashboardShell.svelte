<script>
    import { Link, router } from '@inertiajs/svelte';
    import { motion } from '@humanspeak/svelte-motion';
    import {
        BadgeCheck,
        LayoutDashboard,
        ListChecks,
        LogOut,
        Menu,
        Settings,
        ShieldCheck,
        UserCog,
        UserRound,
        Users,
        X,
    } from '@lucide/svelte';
    import BrandLogo from '@/Components/BrandLogo.svelte';
    import FlashToaster from '@/Components/FlashToaster.svelte';

    export let auth = { user: null };
    export let title = 'داشبورد';

    let mobileMenuOpen = false;

    const routeUrl = (name, fallback) => (typeof route === 'function' ? route(name) : fallback);
    const user = auth?.user ?? null;
    const isManager = Boolean(user?.is_manager);
    const hasAccess = (permission) => Boolean(user?.roles?.includes('general_manager') || user?.permissions?.includes(permission));
    const currentPath = () => window.location.pathname.replace(/\/$/, '') || '/';
    const menuItemMotion = {
        rest: { x: 0, scale: 1 },
        hover: { x: -4, scale: 1.015 },
        tap: { scale: 0.985 },
    };
    const sidePanelMotion = {
        hidden: { x: 36, opacity: 0 },
        show: { x: 0, opacity: 1 },
    };

    $: menuGroups = [
        {
            title: 'گارانتی',
            items: [
                {
                    key: 'warranty-activate',
                    title: 'فعال سازی',
                    href: routeUrl('warranties.activate', '/warranties/activate'),
                    icon: 'shield',
                },
                {
                    key: 'warranty-mine',
                    title: 'گارانتی های من',
                    href: routeUrl('warranties.mine', '/warranties/mine'),
                    icon: 'badge',
                },
                ...(isManager
                    ? [
                          ...(hasAccess('warranties.view_any')
                              ? [
                                    {
                                        key: 'warranty-list',
                                        title: 'لیست گارانتی ها',
                                        href: routeUrl('admin.warranties.index', '/warranties'),
                                        icon: 'list',
                                    },
                                ]
                              : []),
                      ]
                    : []),
            ],
        },
        ...(isManager
            ? [
                  {
                      title: 'کاربران',
                      items: [
                          ...(hasAccess('customers.view_any')
                              ? [
                                    {
                                        key: 'customers-list',
                                        title: 'لیست مشتری ها',
                                        href: routeUrl('admin.customers.index', '/customers'),
                                        icon: 'users',
                                    },
                                ]
                              : []),
                          ...(hasAccess('users.view_any')
                              ? [
                                    {
                                        key: 'users-list',
                                        title: 'لیست کاربران',
                                        href: routeUrl('admin.users.index', '/users'),
                                        icon: 'user-cog',
                                    },
                                ]
                              : []),
                          ...(hasAccess('roles.manage')
                              ? [
                                    {
                                        key: 'roles-list',
                                        title: 'لیست نقش ها',
                                        href: routeUrl('admin.roles.index', '/roles'),
                                        icon: 'roles',
                                    },
                                ]
                              : []),
                      ],
                  },
              ]
            : []),
        ...(isManager && hasAccess('settings.manage')
            ? [
                  {
                      title: 'مدیریت',
                      items: [
                          {
                              key: 'settings',
                              title: 'تنظیمات',
                              href: routeUrl('admin.settings.index', '/settings'),
                              icon: 'settings',
                          },
                      ],
                  },
              ]
            : []),
    ];

    function isActive(href) {
        try {
            return new URL(href, window.location.origin).pathname.replace(/\/$/, '') === currentPath();
        } catch {
            return href === currentPath();
        }
    }

    function iconClass(active) {
        return active ? 'text-[#ec2228]' : 'text-slate-400';
    }

    function logout() {
        router.post(routeUrl('logout', '/logout'));
    }
</script>

<svelte:head>
    <title>{title}</title>
</svelte:head>

<div class="min-h-screen bg-[#f4f8fb] text-slate-950">
    <FlashToaster />
    <aside class="fixed inset-y-0 right-0 z-30 hidden w-72 border-l border-slate-200/80 bg-white/92 px-4 py-5 shadow-[0_24px_80px_rgba(23,39,93,0.08)] backdrop-blur xl:block">
        <div class="flex h-full flex-col">
            <BrandLogo className="mb-8 w-40" />
            <nav class="flex flex-1 flex-col gap-7">
                <motion.div layout variants={menuItemMotion} initial="rest" animate="rest" whileHover="hover" whileTap="tap">
                    <a
                        href={routeUrl('dashboard', '/dashboard')}
                        class={`relative flex h-12 items-center gap-3 overflow-hidden rounded-2xl px-4 text-sm font-black transition ${
                            isActive(routeUrl('dashboard', '/dashboard')) ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/10' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                        }`}
                    >
                        {#if isActive(routeUrl('dashboard', '/dashboard'))}
                            <span class="absolute bottom-0 right-4 h-1 w-10 rounded-full bg-[#ec2228]"></span>
                        {/if}
                        <LayoutDashboard size={18} />
                        داشبورد
                    </a>
                </motion.div>

                {#each menuGroups as group}
                    <div>
                        <div class="mb-2 px-4 text-xs font-black text-slate-400">{group.title}</div>
                        <div class="flex flex-col gap-1.5">
                            {#each group.items as item, i}
                                {@const active = isActive(item.href)}
                                <motion.div layout variants={menuItemMotion} initial="rest" animate="rest" whileHover="hover" whileTap="tap">
                                    <Link
                                        href={item.href}
                                        class={`relative flex h-11 items-center gap-3 overflow-hidden rounded-2xl px-4 text-sm font-bold transition ${
                                            active ? 'bg-red-50 text-slate-950 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                                        }`}
                                    >
                                        {#if active}
                                            <span class="absolute bottom-0 right-4 h-1 w-8 rounded-full bg-[#ec2228]"></span>
                                        {/if}
                                        {#if item.icon === 'shield'}
                                            <ShieldCheck class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'badge'}
                                            <BadgeCheck class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'list'}
                                            <ListChecks class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'users'}
                                            <Users class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'settings'}
                                            <Settings class={iconClass(active)} size={18} />
                                        {:else}
                                            <UserCog class={iconClass(active)} size={18} />
                                        {/if}
                                        {item.title}
                                    </Link>
                                </motion.div>
                            {/each}
                        </div>
                    </div>
                {/each}
            </nav>

            <div class="flex flex-col gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                <div class="text-sm font-black text-slate-950">{user?.name ?? 'کاربر برووات'}</div>
                <div class="mt-1 text-xs font-bold text-slate-500" dir="ltr">{user?.mobile ?? ''}</div>
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-white text-sm font-black text-slate-600 shadow-sm transition hover:bg-red-50 hover:text-[#ec2228] active:scale-95"
                    onclick={logout}
                >
                    <LogOut size={16} />
                    &#1582;&#1585;&#1608;&#1580;
                </button>
            </div>
        </div>
    </aside>

    {#if mobileMenuOpen}
        <button
            type="button"
            class="fixed inset-0 z-40 bg-slate-950/28 backdrop-blur-sm xl:hidden"
            aria-label="بستن منو"
            onclick={() => (mobileMenuOpen = false)}
        ></button>
        <motion.aside
            class="fixed inset-y-0 right-0 z-50 w-[min(21rem,88vw)] bg-white px-4 py-5 shadow-2xl xl:hidden"
            variants={sidePanelMotion}
            initial="hidden"
            animate="show"
            transition={{ type: 'spring', stiffness: 420, damping: 34 }}
        >
            <div class="mb-6 flex items-center justify-between gap-4">
                <BrandLogo className="w-36" />
                <button
                    type="button"
                    class="inline-flex size-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"
                    aria-label="بستن منو"
                    onclick={() => (mobileMenuOpen = false)}
                >
                    <X size={20} />
                </button>
            </div>

            <nav class="flex min-h-[calc(100vh-6.5rem)] flex-col gap-6">
                <motion.div variants={menuItemMotion} initial="rest" animate="rest" whileTap="tap">
                    <Link
                        href={routeUrl('dashboard', '/dashboard')}
                        class={`flex h-12 items-center gap-3 rounded-2xl px-4 text-sm font-black ${
                            isActive(routeUrl('dashboard', '/dashboard')) ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-700'
                        }`}
                        onclick={() => (mobileMenuOpen = false)}
                    >
                        <LayoutDashboard size={18} />
                        داشبورد
                    </Link>
                </motion.div>

                {#each menuGroups as group}
                    <div>
                        <div class="mb-2 px-4 text-xs font-black text-slate-400">{group.title}</div>
                        <div class="flex flex-col gap-1.5">
                            {#each group.items as item, i}
                                {@const active = isActive(item.href)}
                                <motion.div custom={i} variants={menuItemMotion} initial="rest" animate="rest" whileTap="tap">
                                    <Link
                                        href={item.href}
                                        class={`flex h-11 items-center gap-3 rounded-2xl px-4 text-sm font-bold ${
                                            active ? 'bg-red-50 text-slate-950' : 'text-slate-600'
                                        }`}
                                        onclick={() => (mobileMenuOpen = false)}
                                    >
                                        {#if item.icon === 'shield'}
                                            <ShieldCheck class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'badge'}
                                            <BadgeCheck class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'list'}
                                            <ListChecks class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'users'}
                                            <Users class={iconClass(active)} size={18} />
                                        {:else if item.icon === 'settings'}
                                            <Settings class={iconClass(active)} size={18} />
                                        {:else}
                                            <UserCog class={iconClass(active)} size={18} />
                                        {/if}
                                        {item.title}
                                    </Link>
                                </motion.div>
                            {/each}
                        </div>
                    </div>
                {/each}
                <button
                    type="button"
                    class="mt-auto inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-slate-50 px-4 text-sm font-black text-slate-600 transition hover:bg-red-50 hover:text-[#ec2228] active:scale-95"
                    onclick={logout}
                >
                    <LogOut size={17} />
                    &#1582;&#1585;&#1608;&#1580;
                </button>
            </nav>
        </motion.aside>
    {/if}

    <div class="min-h-screen xl:pr-72">
        <header class="sticky top-0 z-20 border-b border-slate-200/70 bg-[#f4f8fb]/86 px-4 py-4 backdrop-blur sm:px-6 xl:px-8">
            <div class="flex items-center justify-between gap-4">
                <button
                    type="button"
                    class="inline-flex size-11 items-center justify-center rounded-2xl bg-white text-slate-700 shadow-sm transition active:scale-95 xl:hidden"
                    aria-label="باز کردن منو"
                    onclick={() => (mobileMenuOpen = true)}
                >
                    <Menu size={22} />
                </button>

                <div class="min-w-0 flex-1">
                    <div class="text-xs font-black text-slate-400">پنل خدمات پس از فروش</div>
                    <h1 class="mt-1 truncate text-xl font-black text-slate-950 sm:text-2xl">{title}</h1>
                </div>

                <Link
                    href={routeUrl('profile.index', '/profile')}
                    class="hidden h-10 items-center gap-2 rounded-full bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition hover:bg-red-50 hover:text-[#ec2228] active:scale-95 sm:inline-flex"
                >
                    <UserRound size={17} />
                    پروفایل
                </Link>
            </div>
        </header>

        <motion.main
            class="min-h-[calc(100vh-5rem)] px-4 py-5 sm:px-6 xl:px-8"
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.28, ease: 'easeOut' }}
        >
            <slot />
        </motion.main>
    </div>
</div>
