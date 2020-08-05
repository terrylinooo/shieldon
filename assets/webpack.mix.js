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

mix.js('./src/js/app.js', './dist/app-packed.js')
mix.sass('./src/sass/app.scss', './dist');
mix.styles([
    'node_modules/bootstrap/dist/css/bootstrap.min.css',
    './dist/app.css'
], './dist/app-packed.css');
