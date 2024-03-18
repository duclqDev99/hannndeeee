<section class="order-customer">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-1.png')}}" class="ns-blog-bg-shape-1 ns-blog-shape-bg" alt="blog-shape-1.png">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-2.png')}}" class="ns-blog-bg-shape-2 ns-blog-shape-bg" alt="blog-shape-2.png">
    <img src="{{RvMedia::getImageUrl('ecommerce/blog-shape-3.png')}}" class="ns-blog-bg-shape-3 ns-blog-shape-bg" alt="blog-shape-3.png">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-12">
                <div class="whitepaper">
                    <h1 class="title text-white">
                        Hãy tạo đơn đặt hàng<br>cho bạn!!
                    </h1>

                    {{-- <div class="book">
                        <div class="gap"></div>
                        <div class="pages">
                            <div class="page"></div>
                            <div class="page"></div>
                            <div class="page"></div>
                            <div class="page"></div>
                            <div class="page"></div>
                            <div class="page"></div>
                        </div>
                        <div class="flips">
                            <div class="flip flip1">
                                <div class="flip flip2">
                                    <div class="flip flip3">
                                        <div class="flip flip4">
                                            <div class="flip flip5">
                                                <div class="flip flip6">
                                                    <div class="flip flip7"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <img src="{{RvMedia::getImageUrl('ecommerce/whitepaper.jpg', null, false, RvMedia::getDefaultImage())}}" class="img-fluid" alt="">
                </div>
            </div>
            <div class="col-lg-7 col-12">
                <form action="{{route('customer-book-order.create.front')}}" method="post" enctype="multipart/form-data" class="form-order">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="username" class="control-label required">Họ và tên</label>
                                <input type="text" id="username" name="username" class="form-control"
                                    placeholder="Nhập họ và tên" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="email" class="control-label required">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="example@gmail.com" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="phone" class="control-label required">Số điện thoại</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="Điện thoại" required>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="address">Địa chỉ</label>
                                <input type="text" id="address" name="address" class="form-control"
                                    placeholder="Nhập địa chỉ">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="type_order" class="control-label required">Chọn loại đơn đặt hàng</label>
                                <select name="type_order" id="type_order" class="form-control" data-value="{{ !empty($_GET['option']) ? $_GET['option'] : '' }}">
                                    <option value="uniform">Đồng phục</option>
                                    <option value="uniform-club">Đồng phục CLB</option>
                                    <option value="custom">Đặt theo yêu cầu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="quantity" class="control-label required">Số lượng đặt</label>
                                <input type="number" id="quantity" min="0" class="form-control" name="quantity" required>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="image">Hình ảnh thiết kế (Nếu có)</label>
                                <input type="file" accept="image/*" id="image" class="form-control" name="image[]" multiple>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="expected_date">Ngày dự kiến cần hàng</label>
                                <input type="date" id="expected_date" class="form-control" name="expected_date">
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="note">Ghi chú</label>
                                <textarea name="note" id="note" class="form-control" placeholder="Ghi chú"></textarea>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-group">
                                @if (setting('enable_captcha') && is_plugin_active('captcha'))
                                    {!! Captcha::display() !!}
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <button type="submit" class="btn--custom btn--outline btn--rounded">Xác nhận</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function(){
            document.querySelectorAll('select').forEach(item => {
                item.querySelectorAll('option').forEach(option => {
                    if(option.value == item.dataset.value) return option.selected = true;
                })
            })
        })
    </script>
</section>
