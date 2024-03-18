let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/jquery-1.11.1.min.js', dist + '/js/jquery-1.11.1.min.js')
    .js(source + '/resources/assets/js/jquery.orgchart.js', dist + '/js/jquery.orgchart.js')
    .js(source + '/resources/assets/js/procedure-order.js', dist + '/js/procedure-order.js')
    .js(source + '/resources/assets/js/script.js', dist + '/js/script.js')
    .js(source + '/resources/assets/js/order-script.js', dist + '/js/order-script.js')


    .css(source + '/resources/assets/css/orgchart.css', dist + '/css/orgchart.css')
    .css(source + '/resources/assets/css/procedure-order.css', dist + '/css/procedure-order.css')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/jquery-1.11.1.min.js', source + '/public/js')
        .copy(dist + '/js/jquery.orgchart.js', source + '/public/js')
        .copy(dist + '/js/procedure-order.js', source + '/public/js')
        .copy(dist + '/js/script.js', source + '/public/js')
        .copy(dist + '/js/order-script.js', source + '/order-script/js')


        .copy(dist + '/css/orgchart.css', source + '/public/css')
        .copy(dist + '/css/procedure-order.css', source + '/public/css')
}
