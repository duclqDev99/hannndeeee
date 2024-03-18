<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ 'plugins/ecommerce::order.invoice_for_order'|trans }} {{ invoice.code }}</title>

    {{ settings.font_css }}

    <style>
        body {
            font-size: 15px;
            font-family: '{{ settings.font_family }}', Arial, sans-serif !important;
        }

        table {
            border-collapse: collapse;
            width: 100%
        }

        table tr td {
            padding: 0
        }

        table tr td:last-child {
            text-align: right
        }

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
            color: #fb7578;
        }

        .logo-container {
            margin: 20px 0 50px
        }

        .invoice-info-container {
            font-size: .875em
        }

        .invoice-info-container td {
            padding: 4px 0
        }

        .line-items-container {
            font-size: .875em;
            margin: 70px 0
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
            width: 38%
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

        {{ settings.extra_css }}
    </style>

    {{ settings.header_html }}

    {{ invoice_header_filter | raw }}
</head>
<body>

{{ invoice_body_filter | raw }}



<table class="invoice-info-container">
    <tr>
        <td>
            <div class="logo-container">
                {% if logo %}
                    <img src="{{ logo_full_path }}" style="width:100%; max-width:150px;" alt="site_title">
                {% endif %}
            </div>
        </td>
        <td>
            {% if invoice.created_at %}
                <p>
                    <strong>{{ invoice.created_at|date('F d, Y') }}</strong>
                </p>
            {% endif %}
           
           
        </td>
    </tr>
</table>

<table class="invoice-info-container">
    <tr>
        <td>
            {% if company_name %}
                <p>{{ company_name }}</p>
            {% endif %}

            {% if company_address %}
                <p>{{ company_address }}</p>
            {% endif %}

            {% if company_phone %}
                <p>{{ company_phone }}</p>
            {% endif %}

            {% if company_email %}
                <p>{{ company_email }}</p>
            {% endif %}

            {% if company_tax_id %}
                <p>{{ 'plugins/ecommerce::ecommerce.tax_id'|trans }}: {{ company_tax_id }}</p>
            {% endif %}
        </td>
        <td>
            {% if invoice.order.customer_name %}
                <p>{{ invoice.customer_name }}</p>
            {% endif %}
            {% if invoice.order.customer_phone %}
                <p>{{ invoice.customer_email }}</p>
            {% endif %}
            
        </td>
    </tr>
</table>

{% if invoice.note %}
    <table class="invoice-info-container">
        <tr style="text-align: left">
            <td style="text-align: left">
                <p>{{ 'plugins/ecommerce::order.note'|trans }}: {{ invoice.note }}</p>
            </td>
        </tr>
    </table>
{% endif %}

<table class="line-items-container">
    <thead>
    <tr>
        <th class="heading-description">Tên thành phẩm</th>
        <th class="heading-description">Size</th>

        <th class="heading-description">Số lượng</th>
        <th class="heading-quantity">Đơn giá</th>
        <th class="heading-price">Tổng tiền</th>
    </tr>
    </thead>
    <tbody>
    {% for item in products %}
        <tr>
            <td>{{ item.product_name }} {% if item.sku %} ({{ item.sku }}) {% endif %}</td>
            <td>{{ item.size }}</td>
            <td>{{ item.qty }}</td>
            <td class="right">{{ item.price }}</td>
            <td class="bold">{{ item.price*item.qty }}</td>
        </tr>
    {% endfor %}

  

   
    </tbody>
</table>


{{ ecommerce_invoice_footer | raw }}
</body>
</html>
