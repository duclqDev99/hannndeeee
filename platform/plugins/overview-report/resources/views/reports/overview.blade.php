
@extends(BaseHelper::getAdminMasterLayoutTemplate())
@push('header-action')
@php
    $optionSelect = json_encode(config('plugins.overview-report.overview-report.selectOption'));
    $permission['agentReceipt'] = request()->user()->hasPermission('agent-receipt.confirm');
    $permission['agentIssue'] = request()->user()->hasPermission('agent-issue.confirm');
    $permission['showroomIssue'] = request()->user()->hasPermission('showroom-issue.confirm');
    $permission['showroomReceipt'] = request()->user()->hasPermission('showroom-receipt.confirm');
    $permission['hubIssue'] = request()->user()->hasPermission('hub-issue.confirm');
    $permission['hubReceipt'] = request()->user()->hasPermission('hub-receipt.confirm');

@endphp
@section('content')
    <overview-report
        :business_type_select="'{{ $optionSelect }}'"
        :data_agent='@json($agentList)'
        :data_showroom='@json($showroomList)'
        :data_hub='@json($hubList)'
        :permission='@json($permission)'
    ></overview-report>

@endsection
