<!-- Modal -->
<div class="modal fade" id="modalSizeChart" tabindex="-1" role="dialog" aria-labelledby="modalSizeChart" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLongTitle">{{ __('Lựa chọn kích thước dành cho bạn') }}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 32px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <nav class="nav-sizechart">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-shirt-tab" data-toggle="tab" href="#nav-shirt"
                            role="tab" aria-controls="nav-shirt" aria-selected="true">Áo</a>
                        <a class="nav-item nav-link" id="nav-wear-tab" data-toggle="tab" href="#nav-wear"
                            role="tab" aria-controls="nav-wear" aria-selected="false">Quần</a>
                        <a class="nav-item nav-link" id="nav-hat-tab" data-toggle="tab" href="#nav-hat"
                            role="tab" aria-controls="nav-hat" aria-selected="false">Nón</a>
                        <a class="nav-item nav-link" id="nav-glove-tab" data-toggle="tab" href="#nav-glove"
                            role="tab" aria-controls="nav-glove" aria-selected="false">Găng tay</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-shirt" role="tabpanel"
                        aria-labelledby="nav-shirt-tab">
                        <img src="{{ RvMedia::getImageUrl('sizechart/size-chart-shirt-1.jpg') }}" class="img-fluid"
                            alt="size-chart">
                    </div>
                    <div class="tab-pane fade" id="nav-wear" role="tabpanel" aria-labelledby="nav-wear-tab">
                        <img src="{{ RvMedia::getImageUrl('sizechart/size-chart-wear-1.jpg') }}" class="img-fluid"
                            alt="size-chart">
                    </div>
                    <div class="tab-pane fade" id="nav-hat" role="tabpanel" aria-labelledby="nav-hat-tab">
                        <img src="{{ RvMedia::getImageUrl('sizechart/size-chart-hat-1.jpg') }}" class="img-fluid"
                            alt="size-chart">
                    </div>
                    <div class="tab-pane fade" id="nav-glove" role="tabpanel" aria-labelledby="nav-glove-tab">
                        <img src="{{ RvMedia::getImageUrl('sizechart/size-chart-glove-1.jpg') }}" class="img-fluid"
                            alt="size-chart">
                    </div>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
