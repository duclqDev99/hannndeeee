let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/utils.js', dist + '/js')
    .js(source + '/resources/assets/js/order-create.js', dist + '/js')
    .js(source + '/resources/assets/js/add-product.js', dist + '/js')
    .js(source + '/resources/assets/js/edit-product.js', dist + '/js')
    .js(source + '/resources/assets/js/upload-contract.js', dist + '/js')
    .js(source + '/resources/assets/js/search-purchase-order.js', dist + '/js')
    .js(source + '/resources/assets/js/create-quotation.js', dist + '/js')
    .js(source + '/resources/assets/js/create-production.js', dist + '/js')
    
if (mix.inProduction()) {
    mix
        .copy(dist + '/js/utils.js', source + '/public/js')
        .copy(dist + '/js/order-create.js', source + '/public/js')
        .copy(dist + '/js/add-product.js', source + '/public/js')
        .copy(dist + '/js/edit-product.js', source + '/public/js')
        .copy(dist + '/js/upload-contract.js', source + '/public/js')
        .copy(dist + '/js/search-purchase-order.js', source + '/public/js')
        .copy(dist + '/js/create-quotation.js', source + '/public/js')
        .copy(dist + '/js/create-production.js', source + '/public/js')

}       

