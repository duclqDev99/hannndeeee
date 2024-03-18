@php
    $productInBatchCount = $batch->productInBatch()->count();
@endphp
<div class="mt-3">
    <div class="card-body">
        <h5 class="card-title">Thông tin lô <span class="fw-bold">#{{ $batch->batch_code }}</span></h5>
        <p class="card-text">{{ $productInBatchCount }} sản phẩm</p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Hình ảnh</th>
                    <th scope="col">Tên sản phẩm</th>
                    <th scope="col">SKU</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batch->productInBatch as $stt => $p)
                    <tr>
                        <th class="text-center" style="width: 50px" scope="row">{{ $stt + 1 }}</th>
                        <td class="text-center" style="width: 120px">
                            <img class="thumb-image thumb-image-cartorderlist" width="75px" height="75px"
                                src="{{ RvMedia::getImageUrl($p->product->image) }}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center flex-wrap">
                                <span href="#" title=" ${item?.product_name}" class="me-2">
                                    {{ $p->product->name }}
                                </span>
                            </div>
                            <small>
                                <span>
                                    Màu: {{ $p->product->variationProductAttributes[0]->title }},
                                </span>
                                <span>
                                    size: {{ $p->product->variationProductAttributes[1]->title }}
                                </span>
                            </small> 
                        </td>
                        <td>{{ $p->product->sku }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
