<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <link href="https://fonts.googleapis.com/css2?family={{ font_name }}:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;1,100;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <style>
        body {
            font-size: 14px;
            font-family: '{{ font_name }}', sans-serif !important;
            color: rgb(63, 63, 63);
            letter-spacing: -0.5px;
        }

        table.table{
            width: 100%;
        }

        table.table-bordered, 
        table.table-bordered td, 
        table.table-bordered th {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
        }

        table.heading{
            table-layout: fixed; /* Đặt chiều rộng cố định cho bảng */
            border-collapse: collapse; /* Gộp việc vẽ đường biên của các ô */
        }

        table.table-bordered tr th{
            font-weight: bold;
        }

        table.table-center,
        table.table-center tr td{
            text-align: center;
            vertical-align: top;
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

        .text-center{
            text-align: center;
        }

        .text-end{
            text-align: right;
        }

        .company_name{
            text-transform: capitalize;
        }

        .title{
            text-transform: uppercase;
            font-weight: bold;
            font-size: 1.5em;
        }

        .txt-total{
            font-size: 1.4em;
            font-weight: bold;
        }

        .sub-header{
            font-size: .85em;
            text-align: start;
        }

        .sub-header p{
            margin-bottom: 0;
        }

        .signature{
            page-break-inside: avoid;
            height: 100px;
        }

        .bold-7{
            font-weight: 700;
            color: #000;
        }

        .uppercase{
            text-transform: uppercase;
        }

        .text-sm-1{
            font-size: 1.2em;
        }

        .italic{
            font-style: italic;
        }
    </style>
</head>

<body>
    <section>
        <table class="table table-center">
            <tbody>
                <tr>
                    <td width="20%"></td>
                    <td>
                        <div class="title text-center">
                            <h3 style="margin-bottom: 0;">HOÁ ĐƠN GIÁ TRỊ GIA TĂNG</h3>
                        </div>
                    </td>
                    <td width="20%" style="">
                        <div class="sub-header">
                            <p>Mẫu số: {{invoice['id']}}</p>
                            <p>Ký hiệu: {{invoice['code']}}</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="text-center">
            <p>Ngày tạo: {{today}}</p>
        </div>

    </section>

    <section style="border-bottom: 1px solid #000;padding-bottom: 10px;">
        <div>
            <p style="margin-bottom: 0;">Tên công ty: {{company_name}}</p>
            <p style="margin-bottom: 0;">Mã số thuế: {{company_tax_id}}</p>
            <p style="margin-bottom: 0;">Địa chỉ: {{company_address}}</p>
            <p style="margin-bottom: 0;">Điện thoại: {{company_phone}}</p>
        </div>
    </section>

    <section style="border-top: 1px solid #000;padding-bottom: 10px;">
        <div>
            <p style="margin-bottom: 0;">Họ tên người mua hàng: {{name_customer}}</p>
            <p style="margin-bottom: 0;">Mã số thuế: </p>
            <p style="margin-bottom: 0;">Số điện thoại: {{phone_customer}}</p>
            <p style="margin-bottom: 0;">Hình thức thanh toán: Quét mã QR</p>
        </div>
    </section>

    <section>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên hàng hoá</th>
                    <th>SL</th>
                    <th>Giá (VNĐ)</th>
                    <th>Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                {% if proposal_detail %}
                    {% for index,val in proposal_detail %}
                        <tr>
                            <td>{{val['index']+1}}</td>
                            <td>{{val['product_name']}} {{val['attr']}}</td>
                            <td>{{val['quantity']}}</td>
                            <td>{{val['price_num']}}</td>
                            <td>{{val['sub_total_num']}}</td>
                        </tr>
                    {% endfor %}
                {% endif %}
            </tbody>
        </table>
        <div>
            <p style="margin-bottom: 0;">Tổng tiền thanh toán: <i>{{total_amount}} VNĐ</i></p>
        </div>
        <div>
            <p>Số tiền viết bằng chữ: <i>{{total_amount_string}} đồng</i></p>
        </div>
    </section>

    <section class="signature" style="margin-top: 20px">
        <table class="table">
            <tr class="info__company group-signature">
                <td style="text-align: center">
                    <span class="bold-7 uppercase text-sm-1">Người mua hàng</span>
                    <p class="italic">(Ký, họ tên)</p>
                </td>
                <td style="text-align: center">
                    <span  class="bold-7 uppercase text-sm-1">Người bán hàng</span>
                    <p class="italic">(Ký, họ tên)</p>
                </td>
            </tr>
            <tr class="info__company">
                <td style="text-align: center">
                    {% if proposal_name %}
                    <div class="signature"></div>
                    <span class="bold-7 uppercase" style="line-height: 5.20mm; word-wrap: break-word">{{proposal_name}}</span>
                    {% endif %}
                </td>
                <td style="text-align: center; vertical-align: start;">
                    {% if receiver_name %}
                    <div class="signature"></div>
                    <span class="bold-7 uppercase" style="line-height: 5.20mm; word-wrap: break-word">{{receiver_name}}</span>
                    {% endif %}
                </td>
            </tr>
        </table>
    </section>
</body>

</html>