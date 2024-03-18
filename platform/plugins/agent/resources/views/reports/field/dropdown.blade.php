
{{-- <select class="btn action-item dropdown-toggle" id="select-order-agent" name="select-order-agent" onchange="handleSelectChange(this)">
    @foreach($agentList as $id => $name)
        <option value="{{ $id }}" {{ $id == $defaultShowroomId ? 'selected' : '' }}>{{ $name }}</option>
    @endforeach
</select> --}}

<select class="form-select select-search-full select-agent select2-hidden-accessible" onchange="handleSelectChange(this)" name="select-order-agent" id="select-order-agent" outlined="outlined" data-bb-toggle="collapse" data-bb-target=".email-fields" data-select2-id="select2-data-select-order-agent" tabindex="-1" aria-hidden="true">
    @foreach($agentList as $id => $name)
        <option value="{{ $id }}" {{ $id == $defaultShowroomId ? 'selected' : '' }}>{{ $name }}</option>
    @endforeach
</select>

<script>
    function handleSelectChange(select) {
        var value = select.value;
        $.ajax({
            url: "{{$route}}",
            type: 'GET',
            data: {
                filter_values: value
            },
            success: function(response) {
                currentHref = $('.btn.action-item.btn-primary span[data-action="create"]').attr('data-href');
                if (currentHref.includes('select_id=')) {
                    var newHref = currentHref.replace(/(select_id=)[^\&]+/, '$1' + value);
                    $('span[data-action="create"]').attr('data-href', newHref);
                } else {
                    var newHref = currentHref + (currentHref.includes('?') ? '&' : '?') + 'select_id=' + value;
                    $('span[data-action="create"]').attr('data-href', newHref);
                }
                Object.keys(window.LaravelDataTables).map((key) => {
                    let table = window.LaravelDataTables[key]
                    let url = new URL(table.ajax.url())
                    url.searchParams.set('filter_values', value)
                    table.ajax.url(url.href).load()
                })
            }
        });
    }
</script>
