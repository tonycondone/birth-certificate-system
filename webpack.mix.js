const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

// Copy vendor assets to correct relative paths
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css/bootstrap.min.css')
   .copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/js/bootstrap.bundle.min.js')
   .copy('node_modules/@fortawesome/fontawesome-free/css/all.min.css', 'public/css/all.min.css')
   .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts')
   .copy('node_modules/sweetalert2/dist/sweetalert2.min.css', 'public/css/sweetalert2.min.css')
   .copy('node_modules/sweetalert2/dist/sweetalert2.min.js', 'public/js/sweetalert2.min.js')
   .copy('node_modules/qr-scanner/qr-scanner.umd.min.js', 'public/js/qr-scanner.umd.min.js');

// Disable mix-manifest.json for this project
mix.disableNotifications(); 