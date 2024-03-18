@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    // dd($procedureOrder);
@endphp

@section('content')
    <div class="table-wrapper">
        <div class="card">
            <div class="card-header" style="justify-content: end;">
                <div class="btn-list">
                    <a class="btn buttons-reload" tabindex="0"
                        aria-controls="botble-procedure-order-tables-procedure-order-table" type="button" href = "{{route('procedure-groups.index')}}">
                        <span>
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                            Quay lại
                        </span>
                    </a>
                    <a href="{{route('procedure-groups.order.create',$id)}}" id = "open-modal-form-procedure-order" class="btn action-item btn-primary" tabindex="0"
                        aria-controls="botble-procedure-order-tables-procedure-order-table" type="button"
                        data-type = "create" data-target = "">
                        <span>
                            <span class="icon-tabler-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d=" M0 0h24v24H0z" fill="none"></path>
                                    <path d="M12 5l0 14"></path>
                                    <path d="M5 12l14 0"></path>
                                </svg>
                            </span>
                            Tạo mới
                        </span>
                    </a>
                </div>
            </div>
            <div class="card-table">
                <div class="table-responsive">
                    <div id="botble-procedure-order-tables-procedure-order-table_wrapper"
                        class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                        <div id="botble-procedure-order-tables-procedure-order-table_processing"
                            class="dataTables_processing panel panel-default" style="display: none;"></div>
                        <table
                            class="table card-table table-vcenter table-striped table-hover dataTable no-footer dtr-inline"
                            id="botble-procedure-order-tables-procedure-order-table" role="grid"
                            aria-describedby="botble-procedure-order-tables-procedure-order-table_info">
                            <thead>
                                <tr role="row">
                                    <th title="ID" width="20"
                                        class="text-center no-column-visibility column-key-0 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" style="width: 20px;" aria-label="IDorderby asc">ID</th>
                                    <th title="Name" class="text-start column-key-1 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" aria-label="Nameorderby asc">Name</th>
                                    <th title="code" class="column-key-2 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" aria-label="codeorderby asc">code</th>
                                    <th title="parent" class="column-key-3 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" aria-label="parentorderby asc">parent</th>
                                    <th title="Cycle point" class="column-key-4 sorting_asc" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" aria-sort="ascending" aria-label="Cycle pointorderby desc">Cycle
                                        point</th>
                                    <th title="status" class="column-key-5 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" aria-label="statusorderby asc">status</th>
                                    <th title="Created At" width="100" class="column-key-6 sorting" tabindex="0"
                                        aria-controls="botble-procedure-order-tables-procedure-order-table" rowspan="1"
                                        colspan="1" style="width: 100px;" aria-label="Created Atorderby asc">Created
                                        At</th>
                                    <th title="Operations" width="140"
                                        class="text-center no-column-visibility sorting_disabled" rowspan="1"
                                        colspan="1" style="width: 140px;" aria-label="Operations">Operations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($procedureOrder as $val)
                                    <tr role="row" class="odd">
                                        <td class="text-center no-column-visibility column-key-0 dtr-control">
                                            {{ $val->id }}</td>
                                        <td class=" text-start  column-key-1">{{ $val->name }}</td>
                                        <td class="  column-key-2">{{ $val->code }}</td>
                                        <td class="  column-key-3">{{ $val->parent_id }}</td>
                                        <td class="column-key-4 sorting_1">{{ $val->cycle_point }}</td>
                                        @if ($val->main_thread_status == 'main_branch')
                                            <td class="  column-key-5"><span
                                                    class=" badge bg-success text-success-fg">Nhánh
                                                    chính</span></td>
                                        @else
                                            <td class="  column-key-5"><span class=" badge status-label"
                                                    style="color: #F5E8C7; background-color: #435585">Nhánh phụ</span></td>
                                        @endif
                                        <td class="  column-key-6">{{ $val->created_at }}</td>
                                        <td class=" text-center no-column-visibility">
                                            <div class="table-actions">
                                                <a data-bs-toggle="tooltip" data-bs-original-title="Edit"
                                                    href="{{route('procedure-groups.order.edit',$val->id)}}"
                                                    class="btn btn-sm btn-icon btn-primary">
                                                    <span class="icon-tabler-wrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-edit" width="24"
                                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path
                                                                d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1">
                                                            </path>
                                                            <path
                                                                d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z">
                                                            </path>
                                                            <path d="M16 5l3 3"></path>
                                                        </svg>


                                                    </span>

                                                    <span class="sr-only">Edit</span>
                                                </a>

                                                <button data-bs-toggle="tooltip" data-bs-original-title="Delete"
                                                    {{-- href="{{route('procedure-groups.order.delete',$val->id)}}" --}}
                                                    data-target="{{route('procedure-groups.order.delete',$val->id)}}"
                                                    class="btn btn-sm btn-icon btn-danger delete-button" data-dt-single-action=""
                                                    data-method="DELETE" data-confirmation-modal="true"
                                                    <span class="icon-tabler-wrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-trash" width="24"
                                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7l16 0"></path>
                                                            <path d="M10 11l0 6"></path>
                                                            <path d="M14 11l0 6"></path>
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="sr-only">Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="card-footer d-flex flex-column flex-sm-row  align-items-center gap-2"
                            style="justify-content: end">
                            <div class="d-flex justify-content-center">
                                <div class="dataTables_paginate paging_simple_numbers"
                                    id="botble-procedure-order-tables-procedure-order-table_paginate">
                                    {{ $procedureOrder->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
