<div class="max-width-1200" id="showroom-user">
    @if ($options['label_show'])
        <label class="{{ $options['label_attr']['class'] }}">{{ $options['label'] }}</label>
    @endif
    <add-showroom-user :data="{{ json_encode($options) }}"></add-showroom-user>
</div>
