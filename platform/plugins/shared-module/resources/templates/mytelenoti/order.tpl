<blockquote><strong>{{sub_title}}</strong></blockquote>
{{ subject }}

<strong>Mã đơn hàng</strong>: {{ order_code }}

<strong>Tổng tiền</strong>: {{ amount|number_format }}

<strong>Đường dẫn</strong>: <a href="{{ order_url }}"> Nhấn vào đây để xem! </a> 

<strong>Trạng thái</strong>: {{ status }}

<strong>Ngày tạo</strong>: {{ created_at }}

<strong>Ghi chú</strong>: {{ note }}
