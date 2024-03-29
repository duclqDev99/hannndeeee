import CreateOrder from './components/SearchProductComponent.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }

            app.directive('ec-modal', {
                mounted(el, bindings) {
                    if (bindings.modifiers && Object.keys(bindings.modifiers).length > 0) {
                        el.addEventListener('click', () => {
                            Object.keys(bindings.modifiers).forEach((modifier) => {
                                $event.emit(`ec-modal:open`, modifier)
                            })
                        })
                    }
                },
            })
            app.component('create-order', CreateOrder)
        },
    })
}