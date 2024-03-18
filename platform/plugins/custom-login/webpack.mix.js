let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .sass(source + '/resources/assets/scss/login.scss', dist + '/css')
    .js(source + '/resources/assets/js/login.js', dist + '/js')
if (mix.inProduction()) {
    mix
        .copy(dist + '/js/login.js', source + '/public/js')
        .copy(dist + '/css/login.css', source + '/public/css')
}
