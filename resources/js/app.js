import { mount } from 'svelte';
import { createInertiaApp } from '@inertiajs/svelte';

import './bootstrap';
import '../css/app.css';

createInertiaApp({
    title: title => title ? `${title} - Browatt Support` : 'Browatt Support',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.svelte', {
            eager: true,
        });

        return pages[`./Pages/${name}.svelte`];
    },
    setup({ el, App, props }) {
        mount(App, {
            target: el,
            props,
        });
    },
});
