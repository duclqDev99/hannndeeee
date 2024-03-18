let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/report-component.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/report-component.js', source + '/public/js')
}
