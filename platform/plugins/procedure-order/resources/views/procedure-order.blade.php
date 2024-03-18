@extends(BaseHelper::getAdminMasterLayoutTemplate())

@php
    // dd($procedureOrder);
@endphp
<style>
    /* The navigation bar */
    .anavbar {
        position: fixed; /* Set the navbar to fixed position */
        bottom: 0; /* Position the navbar at the bottom of the page */
        width: 100%; /* Full width */
        margin-bottom: 10px;
        border: 1px solid #e7e7e7;
        background-color: #f3f3f3;
        opacity: 0.1;
        cursor: pointer;
        z-index: 100
    }

        .anavbar:hover {
            opacity: 1.0;
        }

        .anavbar ul li a {
            display: block;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            float: left;
        }

        .anavbar ul li a:hover {
            background-color: #ccc9c9;
        }

    ul.horizontal {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
</style>
@section('content')

    <body>
        <div class="modal-body">

            <div
                class="max-width-1200"
                id="app"
            >
                <procedure-order :data="{{ $procedureOrder }}" :departments="{{$departmanets}}" ></procedure-order>
            </div>
        </div>
    </body>
@endsection

