<script>
    import DashboardShell from '@/Components/DashboardShell.svelte';

    export let auth = { user: null };
</script>

<DashboardShell title="گارانتی های من" {auth} />
