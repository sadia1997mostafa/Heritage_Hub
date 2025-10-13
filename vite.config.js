import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [
    laravel({
      input: [
  'resources/css/app.css',
  'resources/css/themes.css',
  'resources/css/shop.css',
  'resources/css/ux-3d.css',
  'resources/css/vendor.css', // compile vendor styles
  'resources/css/cart.css',
        'resources/css/admin.css',  // compile admin styles
        'resources/js/app.js',
      ],
      refresh: true,
    }),
    react(),
  ],
});
