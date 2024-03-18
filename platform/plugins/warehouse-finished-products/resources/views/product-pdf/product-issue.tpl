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
        padding: 0
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

    table.table{
        width: 100%;
    }

    
    table.table-center,
    table.table-center tr td{
        text-align: center;
        vertical-align: top;
    }

    table.heading{
        table-layout: fixed; /* Đặt chiều rộng cố định cho bảng */
        border-collapse: collapse; /* Gộp việc vẽ đường biên của các ô */
    }

    table.table-center tr td{
        padding: 0 10px;
    }
    .bold-5{
        font-weight: 500;
    }

    .bold-6{
        font-weight: 600;
    }

    .bold-7{
        font-weight: 700;
        color: #000;
    }

    .h3{
        font-size: 1.4em;
    }

    .uppercase{
        text-transform: uppercase;
    }

    .whitespace-nowrap{
        white-space: nowrap;
    }

    .vertical-middle{
        vertical-align: middle;
    }

    .text-sm-1{
        font-size: .8em;
    }

    .mt-2{
        margin-top: 20px;
    }

    table.table-note{
        width: 100%;
        font-size: 12px;
    }

    table.table-note tr{
        /* padding: 10px; */
    }
</style>

<body>
    <section style="margin-bottom: 50px;">
        <table class="table table-center heading">
            <tr>
                <td class="bold-7 uppercase">{{company_name}}</td>
                <td class="">
                    <div class="h3 uppercase whitespace-nowrap bold-7">Đề nghị xuất kho</div>
                    <div class="whitespace-nowrap mt-2">Ngày {{today}}</div>
                    <div class="whitespace-nowrap">Số: {{code_not_prefix}}</div>
                </td>
                <td>
                    <div class="uppercase">Mẫu số: {{proposal_code}}</div>
                    <div class="text-sm-1">( Ban hành ngày <br> {{date_issued}} )</div>
                </td>
            </tr>
        </table>
    </section>

    <table>
        <tr>
            <td class="">
               - Họ tên người đề nghị: 
               {% if proposal_name %}
                <span class="text-bold">{{proposal_name}}</span>
               {% endif %}
            </td>
            <td class="" colspan="2">
            - Bộ phận: <span class="text-bold"> {{name_department}}</span>
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
                - Kho giao: <span class="text-bold"> {{warehouse_issue_name}}</span>
            </td>
            <td class="">
                - Kho nhận: <span class="text-bold">{{warehouse_receipt_name}}</span>
            </td>
        </tr>
        <tr>
            <td class="" colspan="2">
            - Lý do đề nghị: <span > {{invoice['title']}}</span>
            </td> 
        </tr>
    </table>
    <table id="table_material" class="line-items-container table-bordered">
        <thead>
        <tr>
            <th class="heading-description center" style="width: 5%">STT</th>
            <th class="heading-description center" >Mã hàng hoá
            </th>
            <th class="heading-description center">Tên hàng hóa
            </th>
            <th class="heading-quantity center">Số lượng</th>
            <th class="heading-quantity center">Đơn giá bán</th>
            <th class="heading-quantity center">Chiết khấu %</th>
        </tr>
        </thead>
        <tbody>
        {% if proposal_detail %}
            {% for index,val in proposal_detail %}
                <tr>
                    <td class="center">{{ val['index'] + 1 }}</td>
                    <td class="center">{{val['sku']}}</td>
                    <td>{{ val['product_name'] }} {{ val['size'] ? 'Màu: ' ~ val['color'] ~ ' - Size: ' ~ val['size'] }}</td>
                    <td class="center">{{val['quantity']}} {{(val['color'] == null) ? 'lô' : ''}}</td>
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
                <td>{{total_qty}}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <section>
        <table class="table-note">
            <tr>
                <td>Ghi chú</td>
            </tr>
            <tr>
                <td style="width: 30%;padding-left: 20px;">Khách hàng không lấy hoá đơn</td>
                <td style="width: 20%;"><div style="border: 1px solid #000;height: 20px;width:40px;margin: 0 auto;"></div></td>
                <td style="width: 50%;">Thông tin xuất hoá đơn:</td>
            </tr>
            <tr>
                <td style="width: 30%;padding-left: 20px;">Khách hàng lấy hoá đơn</td>
                <td style="width: 20%;"><div style="border: 1px solid #000;height: 20px;width:40px;margin: 0 auto;"></div></td>
                <td style="width: 50%;">Tên công ty:...........................................</td>
            </tr>
            <tr>
                <td style="width: 30%;padding-left: 20px;"></td>
                <td style="width: 20%;"></td>
                <td style="width: 50%;">Mã số thuế:............................................</td>
            </tr>
        </table>
    </section>

    <div class="signature">
        <table class="invoice-info-container" style="margin-top: 20px" >
            <tr class="info__company group-signature">
                <td style="text-align: center">
                    <span class="text-bold">Người lập biểu</span>
                </td>

                <td style="text-align: center; vertical-align: start;">
                    <span  class="text-bold">Trưởng bộ phận </span>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
