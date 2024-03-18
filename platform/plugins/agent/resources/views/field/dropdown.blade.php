<select class="btn action-item dropdown-toggle" id="mySelectField" onchange="handleSelectChange(this)">
    @foreach($listAgentByUser as $id => $name)
        <option value="{{ $id }}" {{ $id == $defaultAgentId ? 'selected' : '' }}>{{ $name }}</option>
    @endforeach
</select>

<script>
    function handleSelectChange(select) {
        var value = select.value;
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
</script>
