let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/proposal-product.js', dist + '/js')
    .css(source + '/resources/assets/css/form-proposal.css', dist + '/css')
    .js(source + '/resources/assets/js/search-product.js', dist + '/js')
    .js(source + '/resources/assets/js/product-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/approve-product-issue.js', dist + '/js')
    .js(source + '/resources/assets/js/add-warehouse-user.js', dist + '/js')
    .css(source + '/resources/assets/css/product-issue-form.css', dist + '/css')

    .js(source + '/resources/assets/js/batch.js', dist + '/js')
    .js(source + '/resources/assets/js/list-product-stock.js', dist + '/js')
    .js(source + '/resources/assets/js/print-batch-qrcode.js', dist + '/js')
    .js(source + '/resources/assets/js/general-api.js', dist + '/js')
    .js(source + '/resources/assets/js/agent-warehouse.js', dist + '/js')
    .js(source + '/resources/assets/js/detail-product.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-product-receipt.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/proposal-product.js', source + '/public/js')
        .copy(dist + '/css/form-proposal.css', source + '/public/css')
        .copy(dist + '/js/search-product.js', source + '/public/js')
        .copy(dist + '/js/product-issue.js', source + '/public/js')
        .copy(dist + '/js/approve-product-issue.js', source + '/public/js')
        .copy(dist + '/css/product-issue-form.css', source + '/public/css')
        .copy(dist + '/js/batch.js', source + '/public/js')
        .copy(dist + '/js/add-warehouse-user.js', source + '/public/js')
        .copy(dist + '/js/list-product-stock.js', source + '/public/js')
        .copy(dist + '/js/print-batch-qrcode.js', source + '/public/js')
        .copy(dist + '/js/general-api.js', source + '/public/js')
        .copy(dist + '/js/agent-warehouse.js', source + '/public/js')
        .copy(dist + '/js/detail-product.js', source + '/public/js')
        .copy(dist + '/js/proposal-product-receipt.js', source + '/public/js')
}
