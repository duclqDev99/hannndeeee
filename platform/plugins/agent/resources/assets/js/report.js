import SalesReportsChart from './components/SalesReportsChart'
import RevenueChart from './components/RevenueChart'

if (typeof vueApp !== 'undefined') {
    vueApp.booting((vue) => {
        vue.component('sales-reports-chart', SalesReportsChart)
        vue.component('revenue-chart', RevenueChart)
    })
}

$(() => {
    if (!window.moment || !jQuery().daterangepicker) {
        return
    }

    moment.locale($('html').attr('lang'))

    let $dateRange = $(document).find('.date-range-picker')
    let dateFormat = $dateRange.data('format') || 'YYYY-MM-DD'
    let startDate = $dateRange.data('start-date') || moment().subtract(29, 'days')

    let today = moment()
    let endDate = moment().endOf('month')
    if (endDate > today) {
        endDate = today
    }
    let rangesTrans = BotbleVariables.languages.reports
    let ranges = {
        [rangesTrans.today]: [today, today],
        [rangesTrans.this_week]: [moment().startOf('week'), today],
        [rangesTrans.last_7_days]: [moment().subtract(6, 'days'), today],
        [rangesTrans.last_30_days]: [moment().subtract(29, 'days'), today],
        [rangesTrans.this_month]: [moment().startOf('month'), endDate],
        [rangesTrans.this_year]: [moment().startOf('year'), moment().endOf('year')],
    }

    $dateRange.daterangepicker(
        {
            ranges: ranges,
            alwaysShowCalendars: true,
            startDate: startDate,
            endDate: endDate,
            maxDate: endDate,
            opens: 'left',
            drops: 'auto',
            locale: {
                format: dateFormat,
            },
            autoUpdateInput: false,
        },
        function (start, end, label) {
            let selectedOptionValue = $('.form-select.select-agent').val();
            $.ajax({
                url: $dateRange.data('href'),
                data: {
                    date_from: start.format('YYYY-MM-DD'),
                    date_to: end.format('YYYY-MM-DD'),
                    predefined_range: label,
                    agent_id: selectedOptionValue,
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
                                // $('.widget-item').each((key, widget) => {
                                //     const widgetEl = $(widget).prop('id')
                                //     $(`#${widgetEl}`).replaceWith($(data.data).find(`#${widgetEl}`))
                                // })
                            setTimeout(function() {
                                $('.widget-item').each((key, widget) => {
                                    const widgetEl = $(widget).prop('id')
                                    $(`#${widgetEl}`).replaceWith($(data.data).find(`#${widgetEl}`))
                                })
                            }, 1000);

                            Object.keys(window.LaravelDataTables).map((key) => {
                                let table = window.LaravelDataTables[key]
                                let url = new URL(table.ajax.url())
                                $('#export-report-agent').attr('data-start-date', start.format('YYYY-MM-DD'));
                                $('#export-report-agent').attr('data-end-date', end.format('YYYY-MM-DD'));
                                url.searchParams.set('date_from', start.format('YYYY-MM-DD'))
                                url.searchParams.set('date_to', end.format('YYYY-MM-DD'))
                                table.ajax.url(url.href).load()
                            })
                        }
                    }
                },
                error: (data) => {
                    Botble.handleError(data)
                },
            })
        }
    )

    $dateRange.on('apply.daterangepicker', function (ev, picker) {
        let $this = $(this)
        let formatValue = $this.data('format-value')
        if (!formatValue) {
            formatValue = '__from__ - __to__'
        }
        let value = formatValue
            .replace('__from__', picker.startDate.format(dateFormat))
            .replace('__to__', picker.endDate.format(dateFormat))
        $this.find('span').text(value)
    })



    $('.select-agent').on('change',function(){
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
                date_from: startDate.toDateString('YYYY-MM-DD'),
                date_to: endDate.toDateString('YYYY-MM-DD'),
                predefined_range: label,
                agent_id: selectedOptionValue,
            },
            type: 'GET',
            success: (data) => {
                if (data.error) {
                    Botble.showError(data.message)
                } else {
                    $('#export-report-agent').attr('data-agent', selectedOptionValue);
                    if (!$('#report-stats-content').length) {
                        const newUrl = new URL(window.location.href)

                        newUrl.searchParams.set('date_from', start.format('YYYY-MM-DD'))
                        newUrl.searchParams.set('date_to', end.format('YYYY-MM-DD'))

                        history.pushState({ urlPath: newUrl.href }, '', newUrl.href)

                        window.location.reload()
                    }
                    setTimeout(function() {
                        $('.widget-item').each((key, widget) => {
                            const widgetEl = $(widget).prop('id')
                            $(`#${widgetEl}`).replaceWith($(data.data).find(`#${widgetEl}`))
                        })
                    }, 1000);
                    console.log(window.LaravelDataTables);
                    Object.keys(window.LaravelDataTables).map((key) => {
                        let table = window.LaravelDataTables[key]
                        let url = new URL(table.ajax.url())
                        url.searchParams.set('date_from', startDate.toISOString().split('T')[0])
                        url.searchParams.set('date_to', endDate.toISOString().split('T')[0])
                        url.searchParams.set('agent_id', selectedOptionValue)
                        table.ajax.url(url.href).load()
                    })


                }
            },
            error: (data) => {
                Botble.handleError(data)
            },
        })
    })

    $('#export-report-agent').on('click', function (event) {
        const url = $(this).attr('data-target');
        const agentId = $(this).attr('data-agent');
        const startDate = $(this).attr('data-start-date');
        const endDate = $(this).attr('data-end-date');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: url,
            data: {
                agent_id : agentId,
                start_date : startDate,
                end_date : endDate,
            },
            dataType: 'json',
            success: function success(res) {
                if(res.errors){
                    Botble.handleError(res.errors);
                }

                Botble.showSuccess('test','export thành công');
            },
            error: function error(res) {
                Botble.handleError(res.message);
            }
        });
    })
})

