import AgentProposalReceipt from './components/ProposalReceiptComponent.vue'

if (typeof vueApp !== 'undefined') {
    vueApp.registerVuePlugins({
        install(app) {
            app.config.globalProperties.$filters = {
                formatPrice(value) {
                    return parseFloat(value).toFixed(2)
                },
            }
            app.component('agent-proposal-receipt', AgentProposalReceipt)
        },
    })
}


