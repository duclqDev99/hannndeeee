<div class="max-width-1200" id="agent-user">
    @if ($options['label_show'])
        <label class="{{ $options['label_attr']['class'] }}">{{ $options['label'] }}</label>
    @endif
    <add-agent-user :data="{{ json_encode($options) }}"></add-agent-user>
</div>
