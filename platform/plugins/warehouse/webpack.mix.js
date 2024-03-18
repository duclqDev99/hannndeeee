let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/InOutMaterial.js', dist + '/js')
    .js(source + '/resources/assets/js/purchase-goods.js', dist + '/js')
    .js(source + '/resources/assets/js/print-qr-code.js', dist + '/js')
    .js(source + '/resources/assets/js/planMaterial.js', dist + '/js')
    .js(source + '/resources/assets/js/detail_plan_material.js', dist + '/js')
    .css(source + '/resources/assets/css/InOutMaterial.css', dist + '/css')
    .js(source + '/resources/assets/js/proposal-material.js', dist + '/js')
    .js(source + '/resources/assets/js/import-material.js', dist + '/js')
    .js(source + '/resources/assets/js/accept-proposal-out-material.js', dist + '/js')
    .js(source + '/resources/assets/js/qr-scan.js', dist + '/js')
    .js(source + '/resources/assets/js/qr-scan-confirm-out.js', dist + '/js')
    .js(source + '/resources/assets/js/qr-scan-confirm-out-pc.js', dist + '/js')
    .js(source + '/resources/assets/js/proposal-out.js', dist + '/js')
    .js(source + '/resources/assets/js/edit-proposal-material.js', dist + '/js')
    .css(source + '/resources/assets/css/goods-issue.css', dist + '/css')
    // qr-scan-receive
    .js(source + '/resources/assets/js/material-batch-qr-scan.js', dist + '/js')
    .js(source + '/resources/assets/js/material-batch-qr-scan-pc.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/print-qr-code.js', source + '/public/js')
        .copy(dist + '/js/planMaterial.js', source + '/public/js')
        .copy(dist + '/js/detail_plan_material.js', source + '/public/js')
        .copy(dist + '/css/InOutMaterial.css', source + '/public/css')
        .copy(dist + '/js/InOutMaterial.js', source + '/public/js')
        .copy(dist + '/js/purchase-goods.js', source + '/public/js')
        .copy(dist + '/js/proposal-material.js', source + '/public/js')
        .copy(dist + '/js/import-material.js', source + '/public/js')
        .copy(dist + '/js/accept-proposal-out-material.js', source + '/public/js')
        .copy(dist + '/js/qr-scan.js', source + '/public/js')
        .copy(dist + '/js/qr-scan-confirm-out.js', source + '/public/js')
        .copy(dist + '/js/qr-scan-confirm-out-pc.js', source + '/public/js')
        .copy(dist + '/js/proposal-out.js', source + '/public/js')
        .copy(dist + '/js/edit-proposal-material.js', source + '/public/js')
        .copy(dist + '/css/goods-issue.css', source + '/public/css')

        .copy(dist + '/js/material-batch-qr-scan.js', source + '/public/js')
        .copy(dist + '/js/material-batch-qr-scan-pc.js', source + '/public/js')


}
