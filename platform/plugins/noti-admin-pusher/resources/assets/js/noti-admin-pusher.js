import Pusher from 'pusher-js'
$(document).ready(function () {
  let pusherKey = $('#pusher-key').attr('data-key')
  var pusher = new Pusher(pusherKey, {
    cluster: 'ap1',
    encrypted: true,
    authEndpoint: '/broadcasting/auth'
  });
  let permissions;
  $.ajax({
    url: '/admin/get-all-permission-by-user',
    type: 'GET',
    success: function(data) {
        permissions = data
    }
});
  var channel = pusher.subscribe('private-noti-admin-pusher');
  channel.bind('noti-admin-pusher-event', function (response) {
    let data = response.noti
    if(permissions.hasOwnProperty(data.permission)){

      toastr.clear()

      toastr.options = {
        closeButton: true,
        positionClass: 'toast-bottom-right',
        onclick: function () { window.location.href = `${data.action_url}`; },
        showDuration: 1000,
        hideDuration: 10000,
        timeOut: 20000,
        extendedTimeOut: 1000,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut',
      }
      toastr['success'](data.description, data.title)
      if ($('header .navbar-nav .nav-item a[aria-controls="notification-sidebar"] .notification-count').length > 0) {
        $('header .navbar-nav .nav-item a[aria-controls="notification-sidebar"] .notification-count').html(parseInt($('header .navbar-nav .nav-item a[aria-controls="notification-sidebar"] .notification-count').html()) + 1)

      } 
      // else {
      //   $('#open-notification i').after('<span class="badge badge-default">1</span>')
      // }
      
    }

  });
});
