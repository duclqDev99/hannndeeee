<div class="breadcrumb-wrapper">
    <div class="container">
        <ul class="breadcrumb--custom">
            @foreach ($crumbs = Theme::breadcrumb()->getCrumbs() as $i => $crumb)
                @if ($i != (count($crumbs) - 1))
                    <li><a href="{{ $crumb['url'] }}">{!! BaseHelper::clean($crumb['label']) !!}</a></li>
                @else
                    <li class="active">{!! BaseHelper::clean($crumb['label']) !!}</li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
