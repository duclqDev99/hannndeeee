<template>
    <div class="product-code-qr-scan-modal">
        <div class="row">
            <div class="col overflow-auto" id="table_wrapper" style="max-height: 400px">
                <div class="card border-0 position-relative h-100">
                    <div class="card-header border border-bottom-0">
                        <h5 class="card-title fw-bolder">Lịch sử quét</h5>
                    </div>
                    <div class="card-body p-0 d-flex flex-column">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">STT</th>
                                    <th scope="col">QR Code</th>
                                    <th scope="col">Tên sản phẩm</th>
                                    <th scope="col">trạng thái</th>
                                    <th scope="col">Thông tin kho</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in scannedItems" :key="index"
                                    :class="{ 'table-danger': item.danger === 'red' }">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ item.qrCode }}</td>
                                    <td>{{ item.productName }}</td>
                                    <td>{{ item.status }}</td>
                                    <td>{{ item.warehouseInfo }}</td>
                                    <td class="text-center">
                                        <button @click="removeItem(item)" class="btn btn-danger">Xóa</button>
                                    </td>
                                </tr>
                                <tr v-if="scannedItems.length === 0">
                                    <td colspan="5" class="text-center">Chưa có mã QR hợp lệ được quét!</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-3"> <!-- Set width to 90% -->
                <div class="card">
                    <div class="card-body p-2">
                        <div id="scanner_box"
                            class="position-relative w-100 bg-light d-flex gap-3 flex-column align-items-center justify-content-center overflow-hidden"
                            style="height: 270px; border-radius: 5px">
                            <!-- {{-- <div class='scanner'></div> --}} -->
                            <div class="w-25">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-qr-code-scan"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                    <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                    <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                    <path
                                        d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                    <path d="M12 9h2V8h-2z" />
                                </svg>
                            </div>
                            <p class="">Đặt mã QR vào tia máy quét</p>
                        </div>

                        <div id="scanner_box_loading"
                            class="position-relative w-100 d-none gap-3 flex-column align-items-center justify-content-center overflow-hidden"
                            style="height: 270px; border-radius: 5px; background-color: #307ff1">
                            <div class='scanner'></div>
                            <div class="w-25 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-qr-code-scan"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                    <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                    <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                    <path
                                        d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                    <path d="M12 9h2V8h-2z" />
                                </svg>
                            </div>
                            <p class="text-white">Đang kiểm tra mã QR...</p>
                        </div>
                    </div>
                </div>
                <div id="scanner_message" class="mt-3" style="display: none"> </div>
                <div class="col">
                    <button class="btn btn-secondary me-2" :disabled="scannedItems.length == 0 || loading"
                        @click="resetScannedItems">Reset</button>
                    <button class="btn btn-primary" :disabled="scannedItems.length == 0 || loading"
                        @click="saveToExcel(scannedItems, 'danh-sach-qr-quet')">Lưu vào
                        excel</button>
                    <button class="btn btn-success" @click="saveAllToExcel" :disabled="loading">Lưu tất cả QR trong
                        showroom</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Scanner from "../../../../../qr-scan/resources/assets/js/library/scanner";
import axios from 'axios';

export default {
    data() {
        return {
            scannedItems: [], // Data for displaying scanned items
            loadingScan: false,
            isScanning: false,
            PRODUCT_MODEL: "Botble\\Ecommerce\\Models\\Product",
            BATCH_MODEL: "Botble\\WarehouseFinishedProducts\\Models\\ProductBatch",
            SHOWROOM_WAREHOUSE: "Botble\\Showroom\\Models\\ShowroomWarehouse",
            showroom_of_user: [],
            qrProductShowroom: [],
            loading: false
        };
    },
    async mounted() {
        await this.showRoomUser();
        const Scan = new Scanner();
        Scan.onScan(code => {
            try {
                this.loading = true;
                if (!this.isScanning) {
                    this.clearScanMessage();
                    this.openScannerLoading();
                }

                axios.post('/admin/product-qrcodes/ajax-post-qr-scan', { qr_code: code })
                    .then(response => {
                        const data = response.data.data

                        this.closeScannerLoading();
                        if (data.reference_type == this.BATCH_MODEL) {
                            return this.showScanMessage('Bạn đang quét lô, vui lòng quét sản phẩm', 'warning');
                        }
                        if (this.scannedItems.find(item => item.qrCode === data?.qr_code)) {
                            return this.showScanMessage('Sản phẩm đã quét', 'warning');
                        }
                        const dataInsert = {
                            id: data.reference_id,
                            qrCode: data?.qr_code,
                            productName: `${data?.reference?.name} - ${data?.time_create_q_r?.variation_attributes}`,
                            status: data?.status?.label,
                            warehouseInfo: data?.warehouse?.name,
                            danger: data.warehouse_type == this.SHOWROOM_WAREHOUSE && this.showroom_of_user.find(item => item.id === data.warehouse_id) ? '' : 'red',
                        };
                        this.scannedItems.unshift(dataInsert);
                        this.showScanMessage(response.data.message, 'success');

                        // Handle response data here
                    })
                    .catch(error => {
                        this.closeScannerLoading();
                        this.showScanMessage(error.message, 'danger');
                    });

            } catch (err) {
                this.showScanMessage(err.message, 'danger');
            }
            finally {
                this.loading = false;
            }
        });
    },
    methods: {
        saveToExcel(Qr, title) {
            const data = Qr
                .sort((a, b) => a.id - b.id)
                .map(item => ({
                    'QR Code': item.qrCode,
                    'Tên sản phẩm': item.productName,
                    'Trạng thái': item.status,
                    'Thông tin kho': item.warehouseInfo
                }));
            // Chuyển dữ liệu thành chuỗi CSV
            let csvContent = 'data:text/csv;charset=utf-8,';
            csvContent += '\uFEFF'; // BOM để hỗ trợ hiển thị tiếng Việt đúng trên Excel
            csvContent += Object.keys(data[0]).join(',') + '\n'; // Tiêu đề cột

            data.forEach(row => {
                const values = Object.values(row).map(value => `"${value}"`);
                csvContent += values.join(',') + '\n';
            });

            // Tạo một URL từ chuỗi CSV
            const encodedUri = encodeURI(csvContent);

            // Tạo một thẻ a để tải xuống tệp CSV
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', `${title}.csv`);
            document.body.appendChild(link);
            link.click();

            // Xóa thẻ a sau khi đã tải xuống
            document.body.removeChild(link);
        },
        async saveAllToExcel() {
            try {
                this.loading = true;
                const res = await axios.get(route('showroom.get-list_product-showroom-for-user', {
                    showroom: this.showroom_of_user,
                }));
                this.qrProductShowroom.push(...res?.data?.data?.map(item => ({
                    id: item?.reference?.id,
                    qrCode: item?.qr_code,
                    productName: `${item?.reference?.name} - ${item?.time_create_q_r?.variation_attributes}`,
                    status: item?.status?.label,
                    warehouseInfo: item?.warehouse?.name,
                    danger: ''
                })));
                this.saveToExcel(this.qrProductShowroom, 'danh-sach-qr-showroom')

            }
            catch (error) {
                console.error('Đã xảy ra lỗi:', error);
            }
            finally {
                this.loading = false; // Kích hoạt nút khi kết thúc yêu cầu Axios (thành công hoặc thất bại)
            }
        },
        async showRoomUser() {
            try {
                this.loading = true;
                const res = await axios.get(route('showroom.get-list-showroom-for-user'));
                this.showroom_of_user.push(...res?.data?.data?.map(item => ({ id: item.id })));
            }
            catch (error) {
                console.error('Đã xảy ra lỗi:', error);
            }
            finally {
                this.loading = false; // Kích hoạt nút khi kết thúc yêu cầu Axios (thành công hoặc thất bại)
            }

        },
        resetScannedItems() {
            this.scannedItems = []
        },
        removeItem(itemToRemove) {
            this.scannedItems = this.scannedItems.filter(item => item.qrCode !== itemToRemove.qrCode);
        },
        clearScanMessage() {
            $('#scanner_message').html('').hide();
        },
        openScannerLoading() {
            this.isScanning = true;
            $('#scanner_box').removeClass('d-flex').addClass('d-none');
            $('#scanner_box_loading').removeClass('d-none').addClass('d-flex');
        },
        showScanMessage(message, type) {
            $('#scanner_message').html(`
        <div role = "alert" class="alert alert-${type} mt-3 mb-0" >
            <div class="d-flex">
                <div class="w-100">
                    ${message}
                </div>
            </div>
         </div > `).show();
        },
        closeScannerLoading() {
            this.isScanning = false;
            $('#scanner_box').removeClass('d-none').addClass('d-flex');
            $('#scanner_box_loading').removeClass('d-flex').addClass('d-none');
        }
    }

};
</script>


<style scoped>
/* Add any additional styles for the scanner boxes if needed */
</style>