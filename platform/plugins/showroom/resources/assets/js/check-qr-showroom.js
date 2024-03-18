import CheckQRShowroom from './components/CheckQRComponent.vue'
if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('check-qr-showroom', CheckQRShowroom)
        },
    })
}


