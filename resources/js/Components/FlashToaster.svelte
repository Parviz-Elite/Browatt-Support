<script>
    import { usePage } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import { Toaster } from '@/Components/ui/sonner';

    const page = usePage();
    let lastSuccess = null;
    let lastError = null;

    $: success = page.props?.flash?.success ?? null;
    $: error = page.props?.flash?.error ?? null;

    $: if (success && success !== lastSuccess) {
        lastSuccess = success;
        toast.success(success);
    }

    $: if (error && error !== lastError) {
        lastError = error;
        toast.error(error);
    }
</script>

<Toaster position="top-center" richColors closeButton dir="rtl" />
