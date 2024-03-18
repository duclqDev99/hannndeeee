@extends(BaseHelper::getAdminMasterLayoutTemplate())
@push('header-action')
<div>
    <x-core::form.select
    name="email_driver"
    :outlined="true"
    class="select-agent"
    :options="$showroomList"
    :value="$showroom_id"
    data-bb-toggle="collapse"
    data-bb-target=".email-fields"
/>
</div>
<div>
    <x-core::button
        type="button"
        color="primary"
        :outlined="true"
        class="date-range-picker"
        data-format-value="{{ trans('plugins/showroom::reports.date_range_format_value', ['from' => '__from__', 'to' => '__to__']) }}"
        data-format="{{ Str::upper(config('core.base.general.date_format.js.date')) }}"
        data-href="{{ route('showroom.report.index') }}"
        data-start-date="{{ $startDate }}"
        data-end-date="{{ $endDate }}"
        icon="ti ti-calendar"
    >
        {{-- {{ trans('plugins/agent::reports.date_range_format_value', [
            'from' => BaseHelper::formatDate($startDate),
            'to' => BaseHelper::formatDate($endDate),
        ]) }} --}}
    </x-core::button>
</div>
@endpush

@section('content')
    <div id="report-stats-content">
        @include('plugins/showroom::reports.ajax')
    </div>
@endsection

@push('footer')
    <script>
        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = BotbleVariables.languages || {};
        BotbleVariables.languages.reports = {!! json_encode(trans('plugins/showroom::reports.ranges'), JSON_HEX_APOS) !!}
    </script>
@endpush
