import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: env.VITE_ASSET_BASE || '/build/',

        css: {
            preprocessorOptions: {
                scss: {
                    silenceDeprecations: [
                        'import',
                        'global-builtin',
                        'color-functions',
                        'if-function',
                    ],
                },
            },
        },

        plugins: [
            laravel({
                input: [
                    'resources/css/app.scss',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
    };
});
