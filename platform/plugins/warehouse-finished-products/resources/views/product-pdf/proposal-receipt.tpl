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

    <style>
        body {
            font-size: 15px;
            font-family: '{{ settings.font_family }}', sans-serif !important;
            color: rgb(63, 63, 63);
            letter-spacing: -0.5px;
        }

        table {
            border-collapse: collapse;
            width: 100%
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
            font-weight: normal;
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

        .dotted-line {
            display: inline-block;
            width: 100px; /* Đảm bảo chiều rộng của dấu chấm bằng chiều rộng của thẻ cha */
            height: 1px; /* Đặt chiều cao của dấu chấm */
            background-image: radial-gradient(black 1px, transparent 1px); /* Hình chấm đen kích thước 1px và nền trong suốt */
            background-size: 5px 1px; /* Kích thước của hình chấm và khoảng cách giữa chúng */
            background-repeat: repeat-x; /* Lặp lại chấm theo chiều ngang */
        }

        .mt-2{
            margin-top: 20px;
        }

        .signature{
            page-break-inside: avoid;
            height: 100px;
        }

        .text-bold{
            font-weight: bold;
        }

    </style>
</head>

<body>
    <section>
        <table class="table table-center heading">
            <tr>
                <td class="bold-7 uppercase">{{company_name}}</td>
                <td class="">
                    <div class="h3 uppercase whitespace-nowrap bold-7">Đề nghị nhập kho</div>
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

    <section style="margin-top: 50px;">
        <table>
            <tr>
                <td class="">
                    <span>- Họ và tên người đề nghị: <span class="text-bold">{{proposal_name}}</span></span> 							
                </td>
                <td class="">							
                - Bộ phận: <span class="text-bold">{{name_department}}</span>									
                </td>
            </tr>
            {% if warehouse_parent_name %}
            <tr>
                <td class="">
                    - Nơi nhập: 
                    <span class="text-bold">{{ warehouse_parent_name }}</span>
                </td>
                <td class="">
                    - Địa điểm: <span class="text-bold"> {{ warehouse_parent_address != null ? warehouse_parent_address : '..............'}}</span>
                </td>
            </tr>
            {% endif %}
            <tr>
                <td class="">
                    - Kho nhận: <span class="text-bold">{{invoice['warehouse_name']}}</span>							
                </td>
            </tr>
            <tr>
                <td class="">
                    <span>- Lý do đề nghị: {{invoice['title']}}</span>								
                </td>
            </tr>
        </table>

    </section>

    <section class="mt-2">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Stt</th>
                    <th>Mã hàng hoá</th>
                    <th>Tên hàng hoá</th>
                    <th>ĐVT</th>
                    <th>Số lượng</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                {% if proposal_detail %}
                    {% for index,val in proposal_detail %}
                        <tr>
                            <td>{{ val['index']+1 }}</td>
                            <td>{{ val['sku'] }}</td>   
                            <td>{{ val['product_name'] }} {{val['attr1'] != null ? ' - ' ~ val['attr1'] : ''}} {{val['attr2'] != null ? ' - ' ~  val['attr2'] : ''}}</td>
                            <td>{{invoice['is_warehouse'] == 'warehouse' ? 'lô' : ''}} {{invoice['is_warehouse'] == 1 ? 'lô' : ''}}</td>
                            <td>{{ val['quantity'] }}</td>
                            <td></td>
                        </tr>
                    {% endfor %}
                {% endif %}
            </tbody>
        </table>
    </section>

    <section class="signature" style="margin-top: 20px">
        <table class="table">
            <tr class="info__company group-signature">
                <td style="text-align: center">
                    <span class="bold-7 uppercase text-sm-1">Người đề nghị</span>
                </td>
                <td style="text-align: center">
                    <span  class="bold-7 uppercase text-sm-1">Trưởng bộ phận</span>
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