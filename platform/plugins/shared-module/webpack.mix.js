let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .sass(source + '/resources/assets/scss/loading-admin.scss', dist + '/css')
    .sass(source + '/resources/assets/scss/cms.scss', dist + '/css')
    .js(source + '/resources/assets/js/loading-admin.js', dist + '/js')
    .js(source + '/resources/assets/js/location.js', dist + '/js')
if (mix.inProduction()) {
    mix
        .copy(dist + '/js/loading-admin.js', source + '/public/js')
        .copy(dist + '/js/location.js', source + '/public/js')
        .copy(dist + '/css/loading-admin.css', source + '/public/css')
        .copy(dist + '/css/cms.css', source + '/public/css')
}
