import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                'app/View/Components/**',
                'app/Filament/Resources/**',
                'app/Livewire/**',
                'lang/**',
                'resources/lang/**',
                'resources/views/**',
                'routes/**'
            ]
        }),
    ],
});
