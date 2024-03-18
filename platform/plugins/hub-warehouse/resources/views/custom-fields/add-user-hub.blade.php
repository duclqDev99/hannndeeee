<div class="max-width-1200" id="main-order">
    @if ($options['label_show'])
        <label class="{{ $options['label_attr']['class'] }}">{{ $options['label'] }}</label>
    @endif
    <add-hub-user :data="{{ json_encode($options) }}"></add-hub-user>
</div>
