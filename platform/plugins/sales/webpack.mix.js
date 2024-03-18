let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/utils.js', dist + '/js')
    .js(source + '/resources/assets/js/change-step.js', dist + '/js')
    .js(source + '/resources/assets/js/order-create.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/utils.js', source + '/public/js')
        .copy(dist + '/js/change-step.js', source + '/public/js')
        .copy(dist + '/js/order-create.js', source + '/public/js')
}
