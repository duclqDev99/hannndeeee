let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/sale-user.js', dist + '/js')
    .js(source + '/resources/assets/js/sale-proposal-issue.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/sale-user.js', source + '/public/js')
        .copy(dist + '/js/sale-proposal-issue.js', source + '/public/js')
}
