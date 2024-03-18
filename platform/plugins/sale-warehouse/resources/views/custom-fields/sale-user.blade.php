<div class="max-width-1200" id="sale-user">
    @if ($options['label_show'])
        <label class="{{ $options['label_attr']['class'] }}">{{ $options['label'] }}</label>
    @endif
    <sale-user :data="{{ json_encode($options) }}"></sale-user>
</div>
