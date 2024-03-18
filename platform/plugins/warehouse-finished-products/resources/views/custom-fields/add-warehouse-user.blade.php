<div class="max-width-1200" id="warehouse-user">
    @if ($options['label_show'])
        <label class="{{ $options['label_attr']['class'] }}">{{ $options['label'] }}</label>
    @endif
    <add-warehouse-user :data="{{ json_encode($options) }}"></add-warehouse-user>
</div>
