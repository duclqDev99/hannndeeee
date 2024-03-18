<div class="mb-3">
    <label for="widget-name">{{ trans('core/base::forms.name') }}</label>
    <input
        class="form-control"
        name="name"
        type="text"
        value="{{ $config['name'] }}"
    >
</div>
<div class="mb-3">
    <label for="phone">{{ __('Phone') }}</label>
    <input
        class="form-control"
        name="phone"
        type="text"
        value="{{ $config['phone'] }}"
    >
</div>
