<td colspan="10" class="p-0">
    <div class="collapse">
        <div class="card card-body">
            <div class="">
                @foreach ($productDetail as $detail)
                    <div width="100%">
                        @php
                            $total = 0;
                        @endphp
                        <div class="m-3" width="100%">
                            <span class="" style="width: 30%;float: left;">
                                {{ $detail['color'] ? 'Color: ' . $detail['color'] : '' }}
                                {{ $detail['size'] ? 'Size: ' . $detail['size'] : '' }}
                            </span>
                            <span style="width: 60%;  float: left;">
                                @foreach ($detail['stock'] as $stock)
                                @php
                                    $total +=$stock['quantity'];
                                @endphp
                                    {{ $stock['stock'] }} - Số lượng: {{ $stock['quantity'] }}<br>
                                @endforeach
                            </span>
                            <span style="width: 10%; float: left;">
                                Tổng số lượng: {{$total}}
                            </span>
                        </div>
                    </div>
                    <br>
                @endforeach

            </div>
        </div>
    </div>
</td>

<style>

</style>
