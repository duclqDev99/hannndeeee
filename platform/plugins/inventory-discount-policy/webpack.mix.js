let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/discount.js', dist + '/js')


if (mix.inProduction()) {
    mix
        .copy(dist + '/js/discount.js', source + '/public/js')

}
