<div class="panel panel-default">
    @if($config['name'])
    <div class="panel-title">
        <p class="mb-0">- <strong>{!! BaseHelper::clean($config['name']) !!}</strong></p>
    </div>
    @endif

    @if($config['phone'])
        <div class="panel-phone">
            <p> {{__('plugins/widget-custom::widget.front.phone')}}: {!! BaseHelper::clean($config['phone']) !!}</p>
        </div>
    @endif
</div>
