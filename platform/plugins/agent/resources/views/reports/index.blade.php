@extends(BaseHelper::getAdminMasterLayoutTemplate())
@push('header-action')
<div>
    <x-core::button
        id="export-report-agent"
        tag="a"
        color="success"
        icon="fa-solid fa-download"
        data-target="{{ route('agent.report.export-report') }}"
        data-agent="{{$agent_id}}"
        data-start-date="{{ $startDate }}"
        data-end-date="{{ $endDate }}"
    >
        Xuất file
    </x-core::button>
</div>
<div style="width:200px">
    <x-core::form.select
        name="email_driver"
        :outlined="true"
        class="select-agent"
        :options="$agentList"
        :value="$agent_id"
        data-bb-toggle="collapse"
        data-bb-target=".email-fields"
        :searchable="true"
    />
</div>
<div>
    <x-core::button
        type="button"
        color="primary"
        :outlined="true"
        class="date-range-picker"
        data-format-value="{{ trans('plugins/agent::reports.date_range_format_value', ['from' => '__from__', 'to' => '__to__']) }}"
        data-format="{{ Str::upper(config('core.base.general.date_format.js.date')) }}"
        data-href="{{ route('agent.report.index') }}"
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
        @include('plugins/agent::reports.ajax')
    </div>
@endsection

@push('footer')
    <script>
        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = BotbleVariables.languages || {};
        BotbleVariables.languages.reports = {!! json_encode(trans('plugins/agent::reports.ranges'), JSON_HEX_APOS) !!}
    </script>
@endpush
