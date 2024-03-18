@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
    <div class="widget_content row justify-content-center">
        <div class="col-lg-6 col-md-12 card">
            <form action="{{route('product-sample.update', $product)}}" method="post">
                @csrf
                <div class="card-body row">
                    <div class="col-8 mb-3 position-relative">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" class="form-control" name="name" placeholder="__('order.placeholder_product_name')" value="{{$product->name}}"/>
                    </div>
                    <div class="col-4 mb-3 position-relative">
                        <label class="form-label">Đơn vị tính:</label>
                        <input type="text" class="form-control" name="unit" :placeholder="__('order.placeholder_unit')" value="{{$product->unit}}"/>
                    </div>
                    <div class="col-6 mb-3 position-relative">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control" name="sku" :placeholder="__('order.placeholder_sku')" value="{{$product->sku}}"/>
                    </div>
                    <div class="col-6 mb-3 position-relative">
                        <label class="form-label">Giá:</label>
                        <input type="number" class="form-control" name="price" :placeholder="__('order.placeholder_price')" value="{{$product->price}}"/>
                    </div>
                    <div class="col-6 mb-3 position-relative">
                        <label class="form-label">Màu sắc:</label>
                        <input type="text" class="form-control" name="color" :placeholder="__('order.placeholder_color')" value="{{$product->color}}"/>
                    </div>
                    <div class="col-6 mb-3 position-relative">
                        <label class="form-label">Kích thước:</label>
                        <input type="text" class="form-control" name="size" :placeholder="__('order.placeholder_size')" value="{{$product->size}}"/>
                    </div>
                    <div class="col-12 mb-3 position-relative">
                        <label class="form-label">Thành phần:</label>
                        <input type="text" class="form-control" name="ingredient" :placeholder="__('order.placeholder_ingredient')" value="{{$product->ingredient}}"/>
                    </div>
                    <div class="col-12 mb-3 position-relative">
                        <label class="form-label">Mô tả chi tiết:</label>
                        <textarea class="form-control" name="description" :placeholder="__('order.placeholder_description')">{{$product->description}}</textarea>
                    </div>
                    <div class="widget_action">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection