<div class="panel panel-default">
    <div class="panel-title">
        <h4>{!! BaseHelper::clean($config['name']) !!}</h4>
    </div>
    <div class="panel-content">
        <div>{!! do_shortcode(BaseHelper::clean($config['content'])) !!}</div>
    </div>
</div>
