<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <link href="https://fonts.googleapis.com/css2?family={{ font_name }}:ital,wght@0,100;0,300;0,400;0,500;0,600;0,700;1,100;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    {{ settings.font_css }}
    <style>
        body {
            font-size: 12px;
            font-family: '{{ font_name }}', Arial, sans-serif !important;
            /* letter-spacing: -0.5px;
            margin: 0;
            padding: 0; */
            width: 70mm;
        }

        @page{
            margin: 3mm 1mm;
        }

        h1,h2,h3,h4,p{
            margin: 0;
            padding: 0;
        }

        table.table{
            width: 100%;
        }

        table.table-bordered, 
        table.table-bordered td, 
        table.table-bordered th {
            border-collapse: collapse;
        }

        table.table-bordered td, 
        table.table-bordered th {
            padding: 10px;
            vertical-align: top;
        }

        table.heading{
            table-layout: fixed; /* Đặt chiều rộng cố định cho bảng */
            border-collapse: collapse; /* Gộp việc vẽ đường biên của các ô */
        }

        table.table-bordered tr th{
            font-weight: bold;
            text-align: left;
            border-bottom: 1px dotted #000;
        }

        table.table-center,
        table.table-center tr td{
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

        .vertical-top{
            vertical-align: top;
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
            font-size: 1.2em;
            font-weight: bold;
        }

        .proposal{
            font-size: .9em;
            color: #000;
        }

        .d-flex{
            display: flex;
            justify-content: space-between;
        }

        {{ settings.extra_css }}
    </style>

{{ settings.header_html }}
{{ invoice_header_filter | raw }}
</head>

<body>
{{ invoice_body_filter | raw }}
    <section class="vertical-top">
        <div class="company_name text-center">
            <h3 class="uppercase" style="margin-bottom: 0;">Công Ty CP TM Và Bán Lẻ Handee</h3>
        </div>

        {% if showroom_address %}
        <div class="proposal text-center">
            <p style="margin-bottom: 0;">ĐC Showroom: {{showroom_address}}</p>
        </div>
        {% endif %}

        {% if showroom_phone %}
        <div class="proposal text-center">
            <p style="margin-bottom: 0;">ĐT: {{showroom_phone}}</p>
        </div>
        {% endif %}

        <div class="title text-center">
            <p style="margin-bottom: 0;margin-top: 15px;">HOÁ ĐƠN BÁN HÀNG</p>
        </div>

        <table class="table table-normal">
            <tbody>
                <tr>
                    <td class="proposal"><p style="margin-bottom: 0;">Ngày tạo: {{today}}</p></td>
                    <td class="proposal" style="text-align: right;"><p style="margin-bottom: 0;">Số phiếu: {{invoice['code']}}</p></td>
                </tr>
                <tr>
                    <td colspan="2" class="proposal"><p style="margin-bottom: 0;">Khách hàng: {{name_customer}}</p></td>
                </tr>
            </tbody>
        </table>
            

    </section>

    <section class="vertical-top">
        <table class="table table-bordered no-page">
            
            <tbody>
                <tr style="border-bottom: 1px dotted #000;">
                    <td class="bold-5">Mặt hàng</td>
                    <td class="bold-5">SL</td>
                    <td class="bold-5">ĐG</td>
                    <td class="bold-5">T.Tiền</td>
                </tr>
                {% if proposal_detail %}
                    {% for index,val in proposal_detail %}
                        <tr>
                            <td><span class="font-came">{{val['product_name']}}</span> <br> {{val['attr']}}</td>
                            <td>{{val['quantity']}}</td>
                            <td>{{val['price_num']}}</td>
                            <td>{{val['sub_total_num']}}</td>
                        </tr>
                    {% endfor %}
                {% endif %}
                <tr style="border-top: 1px dotted #000;">
                    <td style="padding-bottom: 0;">Tổng SP</td>
                    <td style="padding-bottom: 0;">{{total_qty_doc}}</td>
                    <td style="padding-bottom: 0;"></td>
                    <td style="padding-bottom: 0;text-align: right;">{{invoice['sub_total']|number_format}}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 0;padding-top: 0;">Thuế</td>
                    <td style="padding-bottom: 0;padding-top: 0;"></td>
                    <td style="padding-bottom: 0;padding-top: 0;"></td>
                    <td style="padding-bottom: 0;padding-top: 0;text-align: right;">{{invoice['tax_amount']|number_format}}</td>
                </tr>
                <tr>
                    <td style="padding-top: 0;">Giảm giá</td>
                    <td style="padding-top: 0;"></td>
                    <td style="padding-top: 0;"></td>
                    <td style="padding-top: 0;text-align: right;">{{invoice['discount_amount']|number_format}}</td>
                </tr>
                <tr style="border-top: 1px dotted #000;">
                    <td >Tổng tiền</td>
                    <td ></td>
                    <td ></td>
                    <td style="text-align: right;">{{invoice['amount']|number_format}}</td>
                </tr>
            </tbody>
        </table>
        <div>
            <p class="txt-total">Tổng: <i>{{total_amount_string}} đồng</i></p>
        </div>
        <div>
            <p class="text-center"><i>Xin cảm ơn Quý khách/ Thank you!</i></p>
        </div>
    </section>
    {{ ecommerce_invoice_footer | raw }}
</body>

</html>