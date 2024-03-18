<div
    class="max-width-1200"
    id="main-order"
>
    @if($options['label_show']) <label class="{{$options['label_attr']['class']}}">{{$options['label']}}</label> @endif
    <add-analysis-product :data="{{ json_encode($options) }}" ></add-analysis-product>
</div>
