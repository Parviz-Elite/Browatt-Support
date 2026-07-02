<script>
    import { onMount } from 'svelte';
    import { CalendarDays } from '@lucide/svelte';

    import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';
    import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css';

    export let id = `jdp-${Math.random().toString(36).slice(2)}`;
    export let label = '';
    export let value = '';
    export let placeholder = '1403/01/01';
    export let disabled = false;
    export let required = false;
    export let autocomplete = 'off';
    export let minDate = null;
    export let maxDate = null;
    export let ariaDescribedby = undefined;
    export let inputClass =
        'h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 pl-5 pr-12 text-left text-base font-black text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100';

    let inputElement;

    function syncValue() {
        value = inputElement?.value ?? '';
    }

    onMount(() => {
        window.jalaliDatepicker?.startWatch({
            selector: `#${id}`,
            persianDigits: false,
            autoReadOnlyInput: true,
            hideAfterChange: true,
            showTodayBtn: true,
            showEmptyBtn: true,
            zIndex: 80,
        });
    });
</script>

<label class="block" for={id}>
    {#if label}
        <span class="mb-2 block text-sm font-black text-slate-700">{label}</span>
    {/if}

    <span class="relative block">
        <CalendarDays class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" size={19} />
        <input
            bind:this={inputElement}
            bind:value
            {id}
            data-jdp
            data-jdp-min-date={minDate}
            data-jdp-max-date={maxDate}
            inputmode="numeric"
            dir="ltr"
            {autocomplete}
            {disabled}
            {required}
            aria-describedby={ariaDescribedby}
            class={inputClass}
            {placeholder}
            oninput={syncValue}
            onchange={syncValue}
            onblur={syncValue}
        />
    </span>
</label>

<style>
    :global(jdp-overlay) {
        z-index: 70;
    }

    :global(jdp-container) {
        border: 1px solid rgb(226 232 240);
        border-radius: 1rem;
        box-shadow: 0 22px 70px rgba(23, 39, 93, 0.16);
        color: rgb(15 23 42);
        font-family: YekanBakh, ui-sans-serif, system-ui, sans-serif;
        overflow: hidden;
    }

    :global(jdp-container .jdp-day.selected),
    :global(jdp-container .jdp-day.selected:hover) {
        background: #ec2228;
        color: #fff;
    }

    :global(jdp-container .jdp-day.today) {
        border-color: #17275d;
        color: #17275d;
        font-weight: 900;
    }

    :global(jdp-container .jdp-btn-today),
    :global(jdp-container .jdp-btn-empty),
    :global(jdp-container .jdp-btn-close) {
        border-radius: 0.75rem;
        font-weight: 800;
    }

    @media only screen and (max-width: 481px) {
        :global(jdp-container) {
            border-radius: 1.25rem 1.25rem 0 0;
            padding-bottom: 1rem;
        }
    }
</style>
