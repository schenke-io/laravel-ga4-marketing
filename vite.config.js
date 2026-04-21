import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'resources/js/ga4-tracker.js'),
      name: 'Ga4Tracker',
      fileName: 'ga4-tracker',
      formats: ['iife'],
    },
    outDir: 'resources/js/dist',
    emptyOutDir: true,
    minify: 'esbuild',
    rollupOptions: {
      output: {
        extend: true,
      },
    },
  },
});
