<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
    {% if settings.using_custom_font_for_invoice and settings.custom_font_family %}

    <link href="https://fonts.googleapis.com/css2?family={{ settings.custom_font_family }}:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;1,100;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    {% endif %}
</head>

<style>
    body {
        font-size: 15px;
        font-family: '{{ settings.font_family }}', sans-serif !important;
    }

    table {
        border-collapse: collapse;
        width: 100%
    }

    table tr td {
        padding: 0;
        vertical-align: top;
    }

    /* table tr td:last-child {
        text-align: right
    } */

    .bold, strong, b, .total, .stamp {
        font-weight: 700
    }

    .right {
        text-align: right
    }

    .large {
        font-size: 1.75em
    }

    .total {
        color: #000;
    }

    .logo-container {
        margin: 0px;
    }

    .invoice-info-container {
        font-size: .875em
    }

    .invoice-info-container td {
        padding: 4px 0
    }

    .line-items-container {
        font-size: .875em;
        margin: 20px 0;
    }

    .line-items-container th {
        border-bottom: 2px solid #ddd;
        color: #999;
        font-size: .75em;
        padding: 10px 0 15px;
        text-align: left;
        text-transform: uppercase
    }

    .line-items-container th:last-child {
        text-align: right
    }

    .line-items-container td {
        padding: 10px 0
    }

    .line-items-container tbody tr:first-child td {
        padding-top: 25px
    }

    .line-items-container.has-bottom-border tbody tr:last-child td {
        border-bottom: 2px solid #ddd;
        padding-bottom: 25px
    }

    .line-items-container th.heading-quantity {
        width: 50px
    }

    .line-items-container th.heading-price {
        text-align: right;
        width: 100px
    }

    .line-items-container th.heading-subtotal {
        width: 100px
    }

    .payment-info {
        font-size: .875em;
        line-height: 1.5;
        width: 38%;
    }

    small {
        font-size: 80%
    }

    .stamp {
        border: 2px solid #555;
        color: #555;
        display: inline-block;
        font-size: 18px;
        left: 30%;
        line-height: 1;
        opacity: .5;
        padding: .3rem .75rem;
        position: fixed;
        text-transform: uppercase;
        top: 40%;
        transform: rotate(-14deg)
    }

    .is-failed {
        border-color: #d23;
        color: #d23
    }

    .is-completed {
        border-color: #0a9928;
        color: #0a9928
    }
    .center{
        text-align: center !important;
    }
    table#table_material, table#table_material th, table#table_material td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    table#table_material thead th,
    table#table_material tfoot td{
        font-weight: bold!important;
        color: #000;
        text-align: center;
    }

    table#table_material tbody tr td{
        padding: 5px;
    }

    .info__company p,
    .info__company h2{
        margin: 0px!important;
    }

    .signature{
        height: 100px;
    }

    .invoice-info-container .info__company .text-bold{
        font-weight: bold;
        font-size: 1.2em;
    }

    .invoice-info-container .info__company .uppcase{
        text-transform: uppercase;
    }

    .invoice-info-container .info__company .italic{
        font-style: normal;
        color: #2b2b2b;
        font-weight: 400;
    }

    .info__company.group-signature{
        vertical-align: start;
    }
    table .text-bold{
        font-weight: bold;
    }
    table .uppcase{
        text-transform: uppercase;
    }

    .signature{
        page-break-inside: avoid;
    }
</style>

<body>
    <table class="invoice-info-container">
        <tr>
            <td>
                <div class="logo-container">
                    {% if logo %}
                    <img src="{{ logo_full_path }}" style="width:100%; max-width:150px;" alt="site_title">
                    {% endif %}
                </div>


            </td>

            <td style="text-align: right;">
                <h4  style="text-align: center;">Mẫu số: {{ invoice['id'] }}</h4>
                <p style="text-align: center;">( Ban hành theo Thông tư số 200/2014/TTBTC <br> ngày 22/12/2014 của Bộ Tài chính )</p>
            </td>
        </tr>
    </table>
    <table class="invoice-info-container">
        <tr>
            <td  class="info__company" style="text-align: center">
                <h2>PHIẾU XUẤT KHO</h2>
                {% if today %}
                <p>Ngày {{today}}</p>
                {% endif %}
                <p> Số : <span class="text-bold uppcase"> {{receipt_code}}</span></p>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="">
               - Họ và tên người nhận hàng:
               {% if receiver_name %}
               <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{receiver_name}}</span>
               {% endif %}
            </td>
        </tr>
        <tr>
            <td class="" colspan="2">
            - Theo .............. số ............ ngày ..... tháng ..... năm ..... của ..............................................
            </td>
        </tr>
        {% if warehouse_parent_name %}
        <tr>
            <td class="">
                - Nơi xuất: 
                <span class="text-bold">{{ warehouse_parent_name }}</span>
            </td>
            <td class="">
                - Địa điểm: <span class="text-bold"> {{ warehouse_parent_address != null ? warehouse_parent_address : '..............'}}</span>
            </td>
        </tr>
        {% endif %}
        <tr>
            <td class="">
                - Xuất tại kho: 
                <span class="text-bold"> 
                    {{invoice['warehouse_name']}}
                </span>
            </td>
            <td class="">
            {% if warehouse_parent_address %}
            {% else %}
                - Địa điểm: <span class="text-bold"> {{invoice['warehouse_address'] != null ? invoice['warehouse_address'] : '..............'}}</span>
            {% endif %}
            </td>
        </tr>

        {% if invoice.title %}
        <tr>
            <td class="">
                - Tiêu đề: 
                <span>{{ invoice.title }}</span>
            </td>
        </tr>
        {% endif %}
    </table>
    <table id="table_material" class="line-items-container table-bordered">
        <thead>
        <tr>
            <th rowspan="2" class="heading-description center" style="width: 5%">STT</th>
            <th rowspan="2" class="heading-description center" style="width: 30%">Tên, nhãn hiệu, quy cách, phẩm chất vật tư, dụng cụ sản phẩm, hàng hóa
            </th>
            <th rowspan="2" class="heading-description center">SKU</th>
            <th colspan="2" class="heading-quantity center">Số lượng (SP)</th>
            <th rowspan="2" class="heading-price center">Đơn giá</th>
            <th rowspan="2" class="heading-subtotal center">Thành tiền</th>
        </tr>
        <tr>
            <th class="heading-quantity center">Theo chứng từ</th>
            <th class="heading-quantity center">Thực xuất</th>
        </tr>
        </thead>
        <tbody>
        {% if proposal_detail %}
            {% for index,val in proposal_detail %}
                <tr>
                    <td class="center">{{ index + 1 }}</td>
                    <td>{{val['product_name']}}{{ val['attr1'] ? ', Màu: ' ~ val['attr1'] ~ ' - Size: ' ~ val['attr2'] }}</td>
                    <td class="center">{{val['sku']}}</td>
                    <td class="center">{{val['quantity']}}</td>
                    <td class="center">{{val['actual_qty']}}</td>
                    <td class="center"></td>
                    <td class="center"></td>
                </tr>
            {% endfor %}
        {% endif %}
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td>Tổng cộng:</td>
                <td></td>
                <td>{{total_qty_doc}}</td>
                <td>{{total_start_qty}}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div class="total">
        Tổng số tiền (Viết bằng chữ):
    </div>
    <div class="total" style="width: 100%">
        <span >Số chứng từ gốc kèm theo: .......................................................</span>
    </div>

    <div class="signature">
        <div class="total" style="width: 100%" style="margin-top: 20px;">
            {% if today %}
            <span style="position: absolute; right: 0px; padding-right: 40px;">Ngày {{today}}</span>
            {% endif %}
        </div>
        <table class="invoice-info-container" style="margin-top: 20px" >
            <tr class="info__company group-signature">
                <td style="text-align: center">
                    <span class="text-bold">Người lập biểu</span>
                    <p class="italic">(Ký, họ tên)</p>
                </td>
                <td style="text-align: center">
                    <span  class="text-bold">Người nhận hàng</span>
                    <p class="italic">(Ký, họ tên)</p>
                </td>

                <td style="text-align: center">
                    <span class="text-bold" >Thủ kho</span>
                    <p class="italic">(Ký, họ tên)</p>
                </td>
                <td style="text-align: center; vertical-align: start;">
                    <span  class="text-bold">Kế toán trưởng </span>
                    <p>(Hoặc bộ phận có nhu cầu nhập)</p>
                    <p class="italic">(Ký, họ tên)</p>
                </td>
                <td style="text-align: center; vertical-align: start;">
                    <span  class="text-bold">Giám đốc </span>
                    <p class="italic">(Ký, họ tên, đóng dấu)</p>
                </td>
            </tr>
            <tr class="info__company">
                <td style="text-align: center">
                    {% if proposal_name %}
                    <div class="signature"></div>
                    <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{proposal_name}}</span>
                    {% endif %}
                </td>
                <td style="text-align: center">
                    {% if receiver_name %}
                    <div class="signature"></div>
                    <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{receiver_name}}</span>
                    {% endif %}
                </td>

                <td style="text-align: center">
                    {% if storekeeper_name %}
                    <div class="signature"></div>
                    <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{storekeeper_name}}</span>
                    {% endif %}
                </td>
                <td style="text-align: center; vertical-align: start;">
                    {% if chief_accountant_name %}
                    <div class="signature"></div>
                    <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{chief_accountant_name}}</span>
                    {% endif %}
                </td>
                <td style="text-align: center; vertical-align: start;">
                    {% if receiver_name %}
                    <div class="signature"></div>
                    <span class="text-bold uppcase" style="line-height: 5.20mm; word-wrap: break-word">{{manager_name}}</span>
                    {% endif %}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
