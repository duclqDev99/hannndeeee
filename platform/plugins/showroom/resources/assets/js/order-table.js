$('.form-select.select-agent').on('change',function(){
    var selectedOptionValue = $(this).val();
    var startDate = new Date($('.date-range-picker').data('start-date'));
    var endDate = new Date($('.date-range-picker').data('end-date'));
    var dateRangeText = $('.drp-selected').text();
    var label = 'Custom Range';
    if(dateRangeText != ''){
        var dates = dateRangeText.split(' - ');
        var startDate = new Date(dates[0].trim());
        var endDate = new Date(dates[1].trim());
    }
    $.ajax({
        url: $dateRange.data('href'),
        data: {
            showroom_id: selectedOptionValue,
        },
        type: 'GET',
        success: (data) => {
            if (data.error) {
                Botble.showError(data.message)
            } else {
                if (!$('#report-stats-content').length) {
                const newUrl = new URL(window.location.href)

                    newUrl.searchParams.set('date_from', start.format('YYYY-MM-DD'))
                    newUrl.searchParams.set('date_to', end.format('YYYY-MM-DD'))

                    history.pushState({ urlPath: newUrl.href }, '', newUrl.href)

                    window.location.reload()
                } else {
                    Object.keys(window.LaravelDataTables).map((key) => {
                        let table = window.LaravelDataTables[key]
                        let url = new URL(table.ajax.url())
                        url.searchParams.set('date_from', startDate.toISOString().split('T')[0])
                        url.searchParams.set('date_to', endDate.toISOString().split('T')[0])
                        url.searchParams.set('showroom_id', selectedOptionValue)

                        table.ajax.url(url.href).load()
                    })

                    setTimeout(function() {
                        $('.widget-item').each((key, widget) => {
                            const widgetEl = $(widget).prop('id')
                            $(`#${widgetEl}`).replaceWith($(data.data).find(`#${widgetEl}`))
                        })
                    }, 1000);
                }
            }
        },
        error: (data) => {
            Botble.handleError(data)
        },
    })
})
