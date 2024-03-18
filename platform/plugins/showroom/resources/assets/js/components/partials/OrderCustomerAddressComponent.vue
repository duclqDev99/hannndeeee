<template>
    <ec-modal
        id="add-customer"
        :title="__('order.create_new_customer')"
        :ok-title="__('order.save')"
        :cancel-title="__('order.cancel')"
        @ok="$emit('create-new-customer', $event)"
    >
        <div class="row">
            <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.name') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.name" />
            </div>
            <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.phone') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.phone" :readonly="true" :class="{ 'readonly-input': true }"/>
            </div>
            <div class="col-md-12 mb-3 position-relative">
                <label class="form-label">{{ __('VID') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.vid" :readonly="true" :class="{ 'readonly-input': true }"/>
            </div>
            <!-- <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.address') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.address" />
            </div>
            <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.email') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.email" />
            </div> -->
            <!-- <div class="col-12 mb-3 position-relative">
                <label class="form-label">{{ __('order.country') }}</label>
                <select
                    class="form-select"
                    v-model="child_customer_address.country"
                    @change="loadStates($event)"
                >
                    <option
                        v-for="(countryName, countryCode) in countries"
                        :value="countryCode"
                        v-bind:key="countryCode"
                    >
                        {{ countryName }}
                    </option>
                </select>
            </div>
            <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.state') }}</label>
                <input
                    type="text"
                    class="form-control customer-address-state"
                    v-model="child_customer_address.state"
                />
            </div>
            <div class="col-md-6 mb-3 position-relative">
                <label class="form-label">{{ __('order.city') }}</label>
                <input
                    type="text"
                    class="form-control customer-address-city"
                    v-model="child_customer_address.city"
                />
            </div>
            <div class="col-md-6 mb-3 position-relative" v-if="zip_code_enabled">
                <label class="form-label">{{ __('order.zip_code') }}</label>
                <input type="text" class="form-control" v-model="child_customer_address.zip_code" />
            </div> -->
        </div>
    </ec-modal>
</template>

<script>

export default {
    props: {
        child_customer_address: {
            type: Object,
            default: {},
        },
        zip_code_enabled: {
            type: Number,
            default: 0,
        },
        use_location_data: {
            type: Number,
            default: 0,
        },
        phone_customer: {
            type: Number,
            default: 0,
        },
        name_customer: String,
        vid_customer: Number,
    },

    data: function () {
        return {
            countries: [],
            states: [],
            cities: [],
        }
    },
    watch: {
        phone_customer(newVal) {
            this.child_customer_address.phone = newVal;
        },
        name_customer(newVal) {
            this.child_customer_address.name = newVal;
        },
        vid_customer(newVal) {
            this.child_customer_address.vid = newVal;
        },
    },
    components: {},
    methods: {
        shownEditAddress: function ($event) {
            this.loadCountries($event)

            if (this.child_customer_address.country) {
                this.loadStates($event, this.child_customer_address.country)
            }

            if (this.child_customer_address.state) {
                this.loadCities($event, this.child_customer_address.state)
            }
        },
        loadCountries: function () {
            let context = this
            if (_.isEmpty(context.countries)) {
                axios
                    .get(route('ajax.countries.list'))
                    .then((res) => {
                        context.countries = res.data.data
                        if (!this.child_customer_address.country) {
                            this.child_customer_address.country = 'VN';
                        }
                    })
                    .catch((res) => {
                        Botble.handleError(res.response.data)
                    })
            }
        },
        loadStates: function ($event, country_id) {
            if (!this.use_location_data) {
                return false
            }

            let context = this
            axios
                .get(route('agent.ajax.states-by-country', { country_id: country_id || $event.target.value }))
                .then((res) => {
                    context.states = res.data.data
                    console.log('state',context.states)
                })
                .catch((res) => {
                    Botble.handleError(res.response.data)
                })
        },
        loadCities: function ($event, state_id) {
            if (!this.use_location_data) {
                return false
            }

            let context = this
            axios
                .get(route('ajax.cities-by-state', { state_id: state_id || $event.target.value }))
                .then((res) => {
                    context.cities = res.data.data
                })
                .catch((res) => {
                    Botble.handleError(res.response.data)
                })
        },
    },
}
</script>
<style scoped>
.readonly-input {
    cursor: not-allowed;
}
</style>
