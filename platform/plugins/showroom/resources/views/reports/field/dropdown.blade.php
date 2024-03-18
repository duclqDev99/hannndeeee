<select class="btn action-item dropdown-toggle" id="mySelectField" onchange="handleSelectChange(this)">
    @foreach($listShowroomByUser as $id => $name)
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
