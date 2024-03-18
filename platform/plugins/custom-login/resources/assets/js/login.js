// join socket login
var prefixAuth = 'user', crCountry = '', iti, input = document.getElementById('phone'), countryCodeElm = document.getElementById('countryCode');
var otpForm;

document.addEventListener('DOMContentLoaded', function() {

    telInput.bind("countrychange", function(e) {
        countryCodeElm.value = iti.getSelectedCountryData().dialCode;
        // ct = iti.getSelectedCountryData().iso2;
    });
});

document.addEventListener("DOMContentLoaded", function(event) {
    var gRecaptcha = document.forms["signin-form"] ? document.forms["signin-form"]["g-recaptcha-response"] : null;
    var signinForm = document.getElementById('signin-form');
    let loaderLogo = document.querySelector('.loader-container .cls-2');

    
    if (signinForm) {
        signinForm.onsubmit = function(e) {
            e.preventDefault();
            loaderLogo.classList.add('active');
            let btnNext = document.getElementById('btn_next');
            btnNext.disabled = true;
            let errorMsg = document.querySelector('#error-msg');

            if (errorMsg) {
                errorMsg.innerText = null;
            }
            var Fdata = {
                _token: signinForm.querySelector('input[name="_token"]').value,
                phonenumber: parseInt(signinForm.querySelector('input[name="phonenumber"]').value.replaceAll(' ', '')),
                countrycode: signinForm.querySelector('input[name="countrycode"]').value,
                'g-recaptcha-response': signinForm.querySelector('*[name="g-recaptcha-response"]').value
            };

            jQuery.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                url: signinForm.action,
                data: Fdata,
                type: signinForm.method,

                success: function(data) {
                    loaderLogo.classList.remove('active');

                    if (typeof data === 'string') {
                        var app = document.querySelector(".sign-phone");
                        if (app) {
                            app.innerHTML = data;
                            setTimeout(() => {
                                var otpField = document.getElementById('otp-field');
                                if(otpField){
                                    var t;
                                    for (let index = 0; index < 6; index++) {
                                        t = document.createElement('input');
                                        t.setAttribute('name', 'otp[]');
                                        t.setAttribute('type', 'number');
                                        t.setAttribute('maxlength', 1);
                                        t.addEventListener('input', function(e){
                                            inputOTP(e.target);
                                        })
                                        t.addEventListener('keydown', function(e){
                                            keyDownOTP(e.target, e);
                                        })
                                        otpField.appendChild(t);
                                    }
                                    otpField.firstElementChild.focus();
                                }

                                otpForm = document.getElementById("otp-form");
                                console.log('')
                                otpForm.addEventListener("submit", submitOTP);

                            }, 500);
                        }
                    } else {
                        btnNext.disabled = false;
                        let d = {errors: []};
                        d.errors.push(data.error_msg);
                        renderErrorLogin(d);
                    }

                },
                error: function(data) {
                    btnNext.disabled = false;
                    loaderLogo.classList.remove('active');
                    renderErrorLogin(data.responseJSON)
                },
            });

        }
    }



});





// Tran dan
var telInput = $("#phone"),
    errorMsg = $("#error-msg"),
    validMsg = $("#valid-msg");



// initialise plugin
iti = window.intlTelInput(input, {

    allowExtensions: true,
    formatOnDisplay: true,
    autoFormat: true,
    autoHideDialCode: true,
    autoPlaceholder: true,
    defaultCountry: 'vn',
    ipinfoToken: "yolo",

    nationalMode: false,
    numberType: "MOBILE",
    //onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
    preferredCountries: ['vn','za'],
    preventInvalidNumbers: true,
    separateDialCode: true,
    initialCountry: "auto",
    geoIpLookup: function(callback) {
        $.get("//ipinfo.io", function() {}, "jsonp").always(function(resp) {
            var countryCode = (resp && resp.country) ? resp.country : 'vn';
            callback(countryCode);
        });
    },
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js"
});

var reset = function() {
    telInput.removeClass("error");
    errorMsg.addClass("hide");
    validMsg.addClass("hide");
};

telInput.on("keyup change", reset);


// OTP
const inputs = document.querySelectorAll(".otp-field input");

inputs.forEach((input, index) => {
    input.dataset.index = index;
    input.addEventListener("keyup", handleOtp);
    input.addEventListener("paste", handleOnPasteOtp);
});

function handleOtp(e) {
    /**
     * <input type="text" 👉 maxlength="1" />
     * 👉 NOTE: On mobile devices `maxlength` property isn't supported,
     * So we to write our own logic to make it work. 🙂
     */
    const input = e.target;
    let value = input.value;
    let isValidInput = value.match(/[0-9a-z]/gi);
    input.value = "";
    input.value = isValidInput ? value[0] : "";

    let fieldIndex = input.dataset.index;
    if (fieldIndex < inputs.length - 1 && isValidInput) {
        input.nextElementSibling.focus();
    }

    if (e.key === "Backspace" && fieldIndex > 0) {
        input.previousElementSibling.focus();
    }

    if (fieldIndex == inputs.length - 1 && isValidInput) {
        submit();
    }
}

function handleOnPasteOtp(e) {
    const data = e.clipboardData.getData("text");
    const value = data.split("");
    if (value.length === inputs.length) {
        inputs.forEach((input, index) => (input.value = value[index]));
        submit();
    }
}

function submit() {
    // 👇 Entered OTP
    let otp = "";
    inputs.forEach((input) => {
        otp += input.value;
        input.disabled = true;
        input.classList.add("disabled");
    });
    // Call API below
}


// Tab
$('.form').find('input, textarea').on('keyup blur focus', function(e) {

    var $this = $(this),
        label = $this.prev('label');

    if (e.type === 'keyup') {
        if ($this.val() === '') {
            label.removeClass('active highlight');
        } else {
            label.addClass('active highlight');
        }
    } else if (e.type === 'blur') {
        if ($this.val() === '') {
            label.removeClass('active highlight');
        } else {
            label.removeClass('highlight');
        }
    } else if (e.type === 'focus') {

        if ($this.val() === '') {
            label.removeClass('highlight');
        } else if ($this.val() !== '') {
            label.addClass('highlight');
        }
    }

});

$('.tab a').on('click', function(e) {

    e.preventDefault();

    $(this).parent().addClass('active');
    $(this).parent().siblings().removeClass('active');

    target = $(this).attr('href');

    $('.tab-content > div').not(target).hide();

    $(target).fadeIn(600);

});



function renderErrorLogin(data){
    var rs = '',
        errorElm = document.querySelector('#signin-form #error-msg');
    if (data.errors && errorElm) {
        for (const property in data.errors) {
            rs += ` <div class="error error_${property}">${data.errors[property]}</div>`;
        }
        errorElm.innerHTML = rs;
        errorElm.style.display = 'block';
        grecaptcha.reset();
    }
}




const submitOTP = function(e) {
    e.preventDefault();
    let loaderLogo = document.querySelector('.loader-container .cls-2');
    let btnPrev= document.getElementById('btn_prev');
    let btnNext = document.getElementById('btn_next');
    btnPrev.disabled = true;
    btnNext.disabled = true;
    loaderLogo.classList.add('active');
    
    let errorMsg = document.querySelector('#error-msg');
    if (errorMsg) {
        errorMsg.innerText = null;
    }
    var otp = '',  otpInput = otpForm.querySelectorAll('input[name="otp[]"]');
    otpInput.forEach(v => {
        otp += v.value;
    });

    const lgtg = localStorage.getItem("lgtg");

    var Fdata = {
        _token: otpForm.querySelector('input[name="_token"]').value,
        phone: otpForm.querySelector('input[name="phone"]').value,
        otp: parseInt(otp),
        lgtg: lgtg
    };

    jQuery.ajax({
        headers: {
            'Accept' : 'application/json',
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: otpForm.action,
        data: Fdata,
        type: otpForm.method,

        success: function(data) {

            if (data.hasOwnProperty('redirect')) {
                window.location.href = data.redirect;
            } else {
                renderErrorJson(data);
            }

            localStorage.removeItem("lgtg");

        },
        error: function(data) {
            loaderLogo.classList.remove('active');
            btnPrev.disabled = false;
            btnNext.disabled = false;
            data = data.responseJSON;
            renderErrorJson(data);
        },
    });
}

function renderErrorJson(data, refreshRecaptcha = false){
    var rs = '',
        errorElm = document.querySelector('#error-msg');
    if (data.errors && errorElm) {
        for (const property in data.errors) {
            rs += ` <div class="error error_${property}">${data.errors[property]}</div>`;
        }
        errorElm.innerHTML = rs;
        errorElm.style.display = 'block';
        if(refreshRecaptcha) grecaptcha.reset();
    }
}
const inputOTP = function(e) {
    var elmNext;
    e.value = e.value.replace(/[^0-9]/g, '');
    if (e.value !== '') {
        elmNext = e.nextElementSibling;
        if (elmNext) {
            elmNext.focus();
            elmNext.select();
        } else {
            console.log('btn_next')
            otpForm.querySelector("#btn_next").click();
        }
    }
}
const keyDownOTP = function(e, event) {
    
    var key = event.keyCode || event.charCode;

    if( key == 8 || key == 46 ){
        event.preventDefault();
        if(e.value == ''){
            var elm = e.previousElementSibling;
            elm.value = '';

            if (elm) {
                elm.focus();
                elm.select();
            }
            else{
                elm = e.parentNode.lastElementChild;
                if (elm) {
                    elm.focus();
                    elm.select();
                }
            }
        }
        else{
            e.value = '';
            var elm = e.previousElementSibling;
        }
        return true;
    }

    if( key == 37){
        event.preventDefault();
        var elm = e.previousElementSibling;
        if (elm) {
            elm.focus();
            elm.select();
        }
        else{
            elm = e.parentNode.lastElementChild;
            if (elm) {
                elm.focus();
                elm.select();
            }
        }
        return true;
    }
    if( key == 39){
        event.preventDefault();
        var elm = e.nextElementSibling;
        if (elm) {
            elm.focus();
            elm.select();
        }
        else{
            elm = e.parentNode.firstElementChild;
            if (elm) {
                elm.focus();
                elm.select();
            }
        }
        return true;
    }

}
