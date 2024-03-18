export default class ProductService {

    static postQrScan(scannedCode) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/admin/product-qrcodes/ajax-post-qr-scan',
                method: 'POST',
                data: { qr_code: scannedCode },
                success:resolve,
                error: ({ responseJSON }) => reject(responseJSON)
            });
        });
    }
}