let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/search-product.js', dist + '/js')
    .js(source + '/resources/assets/js/script.js', dist + '/js')
    .js(source + '/resources/assets/js/print-product-qr-code.js', dist + '/js')
    .js(source + '/resources/assets/js/qr-scan.js', dist + '/js')
    .css(source + '/resources/assets/css/style.css', dist + '/css')


if (mix.inProduction()) {
    mix
        .copy(dist + '/js/search-product.js', source + '/public/js')
        .copy(dist + '/js/print-product-qr-code.js', source + '/public/js')
        .copy(dist + '/js/script.js', source + '/public/js')
        .copy(dist + '/js/qr-scan.js', source + '/public/js')
        .copy(dist + '/css/style.css', source + '/public/css')


}
