@extends('core/table::table')
@section('main-table')
    {!! Form::open(['url' => route('material.import'), 'class' => 'import-material']) !!}
    <input type="file" accept=".xlsx, .xls" class="hidden" id="import_json">
    @parent
    {!! Form::close() !!}

@stop
