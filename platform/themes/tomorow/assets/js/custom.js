(function ($) {
    'use strict'

    function clearClassElement(list, classRemove) {
        $(list).each(function (key, item) {
            $(item).removeClass(classRemove)
        })
    }

    function destroySwiper(swiper) {
        if (swiper !== undefined && swiper !== null) {
            swiper.destroy();
            swiper = null;
        }
    }

    function handleResize(swiper) {
        //ẨN mô tả sp
        const productThumb = document.querySelector('.product--detail .product__thumbnail')
        const productDetail = document.querySelector('.product--detail .product__thumbnail_detail .product__content')
        const productWrapper = document.querySelectorAll('.product--detail .product__thumbnail_detail .wrapper')

        //Xoá bỏ swiper
        if (window.innerWidth > 991.98) {
            destroySwiper(swiper);

            if (productDetail) {
                productDetail.style.display = 'block';
            }

            if (productWrapper) {
                productWrapper.forEach(item => {
                    item.style.order = item.dataset.order

                    if (!item.classList.contains('banner-effect')) item.classList.add('banner-effect')
                })
            }

            if (productThumb) {
                if (productThumb.classList.contains('is_sticky')) productThumb.classList.remove('is_sticky')
            }

        } else {//Add swiper
            if (swiper === undefined || swiper === null) {
                swiper = new Swiper('.product--detail .main-swiper-detail', {
                    direction: 'horizontal',
                    slideClass: 'wrapper',
                    wrapperClass: 'product__thumbnail_detail',
                    loop: true,
                });
            }

            if (productDetail) {
                productDetail.style.display = 'none';
            }

            if (productWrapper) {
                productWrapper.forEach(item => {
                    item.style.order = 'unset'

                    if (item.classList.contains('banner-effect')) item.classList.remove('banner-effect')
                })
            }

            if (productThumb) {
                if (!productThumb.classList.contains('is_sticky')) productThumb.classList.add('is_sticky')
            }
        }
    }

    $(document).ready(function () {
        const progressCircle = document.querySelector(".autoplay-progress svg");
        const progressContent = document.querySelector(".autoplay-progress span");
        var swiper = new Swiper(".mySwiper", {
            grabCursor: true,
            effect: "creative",
            loop: true,
            // autoplay: {
            //     delay: 3500,
            //     disableOnInteraction: false,
            // },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            on: {
                autoplayTimeLeft(s, time, progress) {
                    progressCircle.style.setProperty("--progress", 1 - progress);
                    progressContent.textContent = `${Math.ceil(time / 1000)}s`;
                }
            },
            creativeEffect: {
                prev: {
                    shadow: true,
                    translate: [0, 0, -400],
                },
                next: {
                    translate: ["100%", 0, 0],
                },
            },
        });

        const mainSwiperDetail = document.querySelector('.product--detail .main-swiper-detail')

        if (mainSwiperDetail) {

            var mySwiper = new Swiper('.product--detail .main-swiper-detail', {
                // Optional parameters
                direction: 'horizontal',
                slideClass: 'wrapper',
                wrapperClass: 'product__thumbnail_detail',
                loop: true,
            })

            window.addEventListener('resize', function(){
                handleResize(mySwiper)
            });
            handleResize(mySwiper); // Call the function initially

            //Tạo sự kiện scroll cho trang chi tiết sản phẩm
            window.addEventListener('scroll', function () {
                var left = document.querySelector('.product--detail .product__thumbnail');
                var right = document.querySelector('.product--detail .product__info');

                if (left.getBoundingClientRect().bottom > 1211) {
                    if (left.getBoundingClientRect().bottom > right.getBoundingClientRect().bottom) {
                        right.style.position = 'sticky';
                        right.style.top = '150px';
                        right.style.maxHeight = '700px';
                        right.style.overflowY = 'scroll';
                    }
                } else {
                    right.style.position = 'relative';
                    right.style.top = '0';
                    right.style.maxHeight = 'max-content';
                    right.style.overflowY = 'hidden';
                }

                if (window.scrollY === 0 || window.pageYOffset === 0){
                    right.style.position = 'relative';
                    right.style.top = '0';
                    right.style.maxHeight = 'max-content';
                    right.style.overflowY = 'hidden';
                }
            });
        }

        var swiperCollection = new Swiper(".swiperCollection", {
            //   hide: true,
            slidesPerView: 2.5,
            spaceBetween: 10,
            breakpoints: {
                268: {
                    slidesPerView: 1.5,
                    spaceBetween: 10,
                },
                768: {
                    slidesPerView: 2.5,
                    spaceBetween: 10,
                },
            },
        });

        // project active
        const projectActive = new Swiper(".project-active", {
            slidesPerView: 1,
            effect: "creative",
            grabCursor: true,
            loop: true,
            creativeEffect: {
                prev: {
                    shadow: false,
                    translate: [0, 0, -400],
                },
                next: {
                    shadow: false,
                    translate: ["100%", 0, 0],
                },
            },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
        });

        // project active
        const productMainSlider = new Swiper(".product-main-slider", {
            slidesPerView: 1,
            loop: true,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        //Tạo sự kiện hover cho danh sách sản phẩm
        $(document).find('.product').each(function (index, item) {
            //Event for hover banner image product
            $(item).find('.product__thumbnail').hover(function (event) {
                let context = $(this)

                const imgDefault = $(item).find('.widget-thumb .img-default')
                const imgThumb = $(item).find('.widget-thumb .product-main-slider')

                imgDefault.hide()
                imgThumb.show()
            }, function (event) {
                //Xử lý sự kiện khi rời chuột khỏi phần tử
                const imgDefault = $(item).find('.widget-thumb .img-default')
                const imgThumb = $(item).find('.widget-thumb .product-main-slider')

                imgDefault.show()
                imgThumb.hide()
            })
        })

        //   odometer
        $('.about_count').appear(function (e) {
            var odo = $(".about_count");
            odo.each(function () {
                var countNumber = $(this).attr("data-count");
                $(this).html(countNumber);
            });
        });

        //   odometer 2
        $('.counter_count').appear(function (e) {
            var odo = $(".counter_count");
            odo.each(function () {
                var countNumber = $(this).attr("data-count");
                $(this).html(countNumber);
            });
        });


        let attrProduct = $('.text-swatches-wrapper')

        let itemAttr = attrProduct.find('.attribute-swatch-item')

        let checkMessage = false

        itemAttr.each(function(index,item){
            if($(item).hasClass('pe-none')){
                $(item).find('input[type=radio]').prop('checked', false);
            }else{
                if($(item).find('input[type=radio]').attr('checked') == 'checked'){
                    checkMessage = true
                }
            }
        })

        if(checkMessage){
            $('.number-items-available').html('<span class="text-success">(Trong kho)</span>').show()
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const orderId =   $('#widget__render-img').find('#order-id').val()
        if(orderId)
        {
            $.ajax({
                    url: `/api/v1/order-transactions-client/send-request-payment/${orderId}`,
                    method: "POST",
                    success: res => {
                        if (!res.error_code) {
                            $('#widget__render-img').find('.img-fluid')
                                .attr('src', res?.data?.qr_image);
                        }
                    },
                    error: res => console.log(res)
                });
        }
    })
    $.fn.appear = function (fn, options) {

        var settings = $.extend({

            //arbitrary data to pass to fn
            data: undefined,

            //call fn only on the first appear?
            one: true,

            // X & Y accuracy
            accX: 0,
            accY: 0

        }, options);

        return this.each(function () {

            var t = $(this);

            //whether the element is currently visible
            t.appeared = false;

            if (!fn) {

                //trigger the custom event
                t.trigger('appear', settings.data);
                return;
            }

            var w = $(window);

            //fires the appear event when appropriate
            var check = function () {

                //is the element hidden?
                if (!t.is(':visible')) {

                    //it became hidden
                    t.appeared = false;
                    return;
                }

                //is the element inside the visible window?
                var a = w.scrollLeft();
                var b = w.scrollTop();
                var o = t.offset();
                var x = o.left;
                var y = o.top;

                var ax = settings.accX;
                var ay = settings.accY;
                var th = t.height();
                var wh = w.height();
                var tw = t.width();
                var ww = w.width();

                if (y + th + ay >= b &&
                    y <= b + wh + ay &&
                    x + tw + ax >= a &&
                    x <= a + ww + ax) {

                    //trigger the custom event
                    if (!t.appeared) t.trigger('appear', settings.data);

                } else {

                    //it scrolled out of view
                    t.appeared = false;
                }
            };

            //create a modified fn with some additional logic
            var modifiedFn = function () {

                //mark the element as visible
                t.appeared = true;

                //is this supposed to happen only once?
                if (settings.one) {

                    //remove the check
                    w.unbind('scroll', check);
                    var i = $.inArray(check, $.fn.appear.checks);
                    if (i >= 0) $.fn.appear.checks.splice(i, 1);
                }

                //trigger the original fn
                fn.apply(this, arguments);
            };

            //bind the modified fn to the element
            if (settings.one) t.one('appear', settings.data, modifiedFn);
            else t.bind('appear', settings.data, modifiedFn);

            //check whenever the window scrolls
            w.scroll(check);

            //check whenever the dom changes
            $.fn.appear.checks.push(check);

            //check now
            (check)();
        });
    };

    //keep a queue of appearance checks
    $.extend($.fn.appear, {

        checks: [],
        timeout: null,

        //process the queue
        checkAll: function () {
            var length = $.fn.appear.checks.length;
            if (length > 0) while (length--) ($.fn.appear.checks[length])();
        },

        //check the queue asynchronously
        run: function () {
            if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
            $.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
        }
    });

    //run checks when these methods are called
    $.each(['append', 'prepend', 'after', 'before', 'attr',
        'removeAttr', 'addClass', 'removeClass', 'toggleClass',
        'remove', 'css', 'show', 'hide'], function (i, n) {
            var old = $.fn[n];
            if (old) {
                $.fn[n] = function () {
                    var r = old.apply(this, arguments);
                    $.fn.appear.run();
                    return r;
                }
            }
        });
})(jQuery)
