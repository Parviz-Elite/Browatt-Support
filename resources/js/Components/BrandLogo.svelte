<script>
    export let variant = 'default';
    export let className = '';

    const logos = {
        default: '/images/brand/browatt-logo.svg',
        white: '/images/brand/browatt-logo-w.svg',
    };
</script>

<img
    src={logos[variant] ?? logos.default}
    alt="Browatt"
    class={`block h-auto ${className}`}
    loading="eager"
/>
