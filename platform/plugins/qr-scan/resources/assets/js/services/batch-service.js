export default class BatchService {

    static postQrScan(scannedCode) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/admin/receipt-product/ajax-post-qr-scan',
                method: 'POST',
                data: { qr_code: scannedCode },
                success: resolve,
                error: ({ responseJSON }) => reject(responseJSON)
            });
        });
    }
}