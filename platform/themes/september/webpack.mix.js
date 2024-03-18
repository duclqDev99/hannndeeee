let mix = require('laravel-mix');
const purgeCss = require('@fullhuman/postcss-purgecss');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/themes/' + directory;
const dist = 'public/themes/' + directory;

mix
    .sass(
        source + '/assets/sass/style.scss',
        dist + '/css',
        {},
        [
            purgeCss({
                content: [
                    source + '/assets/js/components/*.vue',
                    source + '/layouts/*.blade.php',
                    source + '/partials/*.blade.php',
                    source + '/partials/**/*.blade.php',
                    source + '/views/*.blade.php',
                    source + '/views/**/*.blade.php',
                    source + '/views/**/**/*.blade.php',
                    source + '/views/**/**/**/*.blade.php',
                    source + '/widgets/**/templates/frontend.blade.php',
                ],
                defaultExtractor: content => content.match(/[\w-/.:]+(?<!:)/g) || [],
                safelist: [
                    /^owl-/,
                    /^button-loading/,
                    /^slick-/,
                    /^noUi-/,
                    /^pagination/,
                    /^page-/,
                    /^btn--/,
                    /^fa-/,
                    /show-admin-bar/,
                    /active/,
                    /show/
                ],
            })
        ])
    .sass(source + '/assets/sass/rtl-style.scss', dist + '/css')

    .js(source + '/assets/js/script.js', dist + '/js')
    .js(source + '/assets/js/backend.js', dist + '/js')

    .copy(dist + '/css/style.css', source + '/public/css')
    .copy(dist + '/css/rtl-style.css', source + '/public/css')
    .copy(dist + '/js/script.js', source + '/public/js')
    .copy(dist + '/js/backend.js', source + '/public/js')
