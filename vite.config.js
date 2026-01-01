import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public/assets',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: 'resources/js/app.js',
            external: ['fsevents'],
        },
    },
    resolve: {
        // Helps with Windows path issues
        preserveSymlinks: false,
    },
});