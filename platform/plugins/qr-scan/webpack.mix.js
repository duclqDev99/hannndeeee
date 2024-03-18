let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/scan-warehouse-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-create-batch.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-batch.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-product-sell.js', dist + '/js')
    .css(source + '/resources/assets/css/scan-loading.css', dist + '/css')
    .js(source + '/resources/assets/js/scan-batch-in.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-info.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-product-to-order-showroom.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-warehouse-receipt.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-warehouse-issue-start.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-warehouse-issue-in-sales.js', dist + '/js')
    .js(source + '/resources/assets/js/scan-sale-warehouse-issue-in-sales.js', dist + '/js')




if (mix.inProduction()) {
    mix
        .copy(dist + '/js/scan-warehouse-issue.js', source + '/public/js')
        .copy(dist + '/js/scan-create-batch.js', source + '/public/js')
        .copy(dist + '/js/scan-batch.js', source + '/public/js')
        .copy(dist + '/js/scan-product-sell.js', source + '/public/js')
        .copy(dist + '/css/scan-loading.css', source + '/public/css')
        .copy(dist + '/js/scan-batch-in.js', source + '/public/js')
        .copy(dist + '/js/scan-info.js', source + '/public/js')
        .copy(dist + '/js/scan-product-to-order-showroom.js', source + '/public/js')
        .copy(dist + '/js/scan-warehouse-receipt.js', source + '/public/js')
        .copy(dist + '/js/scan-warehouse-issue-start.js', source + '/public/js')
        .copy(dist + '/js/scan-warehouse-issue-in-sales.js', source + '/public/js')
        .copy(dist + '/js/scan-sale-warehouse-issue-in-sales.js', source + '/public/js')

}
