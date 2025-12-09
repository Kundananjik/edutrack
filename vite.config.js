import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public/assets',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            // Use a relative path from the project root
            input: 'resources/js/app.js',
        },
    },
});