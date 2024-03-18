const country = 'select[data-type="country"]'
const province = 'select[data-type="province"]'
const district = 'select[data-type="district"]'
const ward = 'select[data-type="ward"]'


$(document).on('DOMContentLoaded', function () {

    $(document).on('change', country, function (e) {
        e.preventDefault()

        const $parent = getParent($(e.currentTarget))
        const $province = $parent.find(province)
        const $district = $parent.find(district)
        const $ward = $parent.find(ward)

        $province.find('option:not([value=""]):not([value="0"])').remove()
        $district.find('option:not([value=""]):not([value="0"])').remove()
        $ward.find('option:not([value=""]):not([value="0"])').remove()

        const $button = $(e.currentTarget).closest('form').find('button[type=submit], input[type=submit]')
        const countryId = $(e.currentTarget).val()

        if (countryId) {
            if ($province.length) {
                getprovinces($province, countryId, $button)
                getCities($district, null, $button, countryId)
            } else {
                getCities($district, null, $button, countryId)
            }
        }

        if (get_rates_url) getShippingRates(get_rates_url);

    })

    $(document).on('change', province, function (e) {

        e.preventDefault()
        const $parent = getParent($(e.currentTarget))
        const $district = $parent.find(district)
        const $ward = $parent.find(ward)

        $district.find('option:not([value=""]):not([value="0"])').remove()
        $ward.find('option:not([value=""]):not([value="0"])').remove()
        if ($district.length) {
            const provinceId = $(e.currentTarget).val()
            const $button = $(e.currentTarget).closest('form').find('button[type=submit], input[type=submit]')
            if (provinceId) {
                getCities($district, provinceId, $button)
            } else {
                const countryId = $parent.find(country).val()
                getCities($district, null, $button, countryId)
            }

            // provinceFieldUsingSelect2()
        }
        // getShippingRates();
    })

    $(document).on('change', district, function (e) {

        const $parent = getParent($(e.currentTarget))
        const $district = $parent.find(district)
        const $ward = $parent.find(ward)
        $.ajax({
            url: $($ward).first().data('url'),
            type: 'GET',
            data: {
                district_id: $($district).first().val(),
            },
            success: function (response) {
                $ward.find('option').remove()

                // Add new options
                $.each(response.data, function (key, value) {
                    $($ward).append('<option value="' + value.viettel_id + '">' + value.viettel_name + '</option>');
                });
            },
            error: function (error) {
                // Handle the error here
            }
        });
        // getShippingRates();

    })

    function getParent($el) {
        let $parent = $(document)
        let formParent = $el.data('form-parent')
        if (formParent && $(formParent).length) {
            $parent = $(formParent)
        }

        return $parent
    }
    function getCities($el, provinceId, $button = null, countryId = null) {
        $.ajax({
            url: $el.data('url'),
            data: {
                state_id: provinceId,
                country_id: countryId,
            },
            type: 'GET',
            beforeSend: () => {
                $button && $button.prop('disabled', true)
            },
            success: (res) => {
                if (res.error) {
                    Botble.showError(res.message)
                } else {
                    let options = ''
                    $.each(res.data, (index, item) => {
                        options += '<option value="' + (item.id || '') + '">' + item.name + '</option>'
                    })

                    $el.html(options)
                    $el.trigger('change')
                }
            },
            complete: () => {
                $button && $button.prop('disabled', false)
            },
        })
    }

})
