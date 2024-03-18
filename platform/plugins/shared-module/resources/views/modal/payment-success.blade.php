<style>
    .payment-success{
        display: flex;
        justify-content: center;
    }

    .payment-success h1 {
        color: #88B04B;
        font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
        font-weight: 900;
        font-size: 40px;
        margin: 0;
    }
    .payment-success p {
        color: #404F5E;
        font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
        font-size:20px;
        margin: 0;
    }
    .payment-success i {
    color: #6f66bc;
    font-size: 100px;
    line-height: 200px;
    margin-left:-15px;
    }

    .payment-success .lottie-auto{
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    }
    .payment-success #lottieAnimation {
    width: 300px;
    height: 300px;
    }
</style>
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentSuccess" data-bs-whatever="@mdo"></button>
<div class="modal fade" id="paymentSuccess" tabindex="-1" aria-labelledby="paymentSuccess" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="payment-success">
                    <div class="text-center">
                        <div class="lottie-auto">
                            <div id="lottieAnimation"></div>
                        </div>
                        <h1>Thành công!</h1> 
                        <p class="mt-2">Lorem, ipsum dolor sit amet consectetur adipisicing elit consectetur adipisicing elit.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.7/lottie.min.js"></script>
<script>
// Tạo một đối tượng Lottie từ tệp tin .lottie và hiển thị nó trong một div
var animation = bodymovin.loadAnimation({
    container: document.getElementById('lottieAnimation'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: "{{ RvMedia::getImageUrl('Animation - 1706588168186.json') }}", // Đường dẫn tới tệp tin .lottie
});
</script>