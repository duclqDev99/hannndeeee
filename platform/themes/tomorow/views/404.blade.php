@php
    SeoHelper::setTitle(__('404 - Not found'));
    Theme::fireEventGlobalAssets();
    Theme::breadcrumb()->add(__('Home'), route('public.index'))->add(SeoHelper::getTitle());
@endphp

{!! Theme::partial('header') !!}
{!! Theme::partial('breadcrumbs') !!}

<main class="page--inner error-inner">
    <div class="container">
        <h1>{{ SeoHelper::getTitle() }}</h1>
        <h4>{{ __('This may have occurred because of several reasons') }}</h4>
        <ul>
            <li>{{ __('The page you requested does not exist.') }}</li>
            <li>{{ __('The link you clicked is no longer.') }}</li>
            <li>{{ __('The page may have moved to a new location.') }}</li>
            <li>{{ __('An error may have occurred.') }}</li>
            <li>{{ __('You are not authorized to view the requested resource.') }}</li>
        </ul>
        <p>{!! BaseHelper::clean(__('Please try again in a few minutes, or alternatively return to the homepage by <a href=":link">clicking here</a>.', ['link' => route('public.index')])) !!}</p>
    </div>
</main>

{!! Theme::partial('footer') !!}
