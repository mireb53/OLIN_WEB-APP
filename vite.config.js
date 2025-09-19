import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/admin_layouts.css',
                'resources/css/admin/user_management.css',
                'resources/css/admin/course_management.css',
                'resources/css/admin/reports_logs.css',
                'resources/css/admin/help.css',
                'resources/css/admin/admin_account.css',
                'resources/js/admin/admin_account.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
