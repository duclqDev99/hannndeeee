const { size } = require("lodash");

toastr.options = {
    closeButton: true,
    positionClass: 'toast-bottom-right',
    showDuration: 1000,
    hideDuration: 1000,
    timeOut: 1000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut',
}


let isConfirmHideModal = false;
let products = [];
let objectUrls = [];
let editId = null;

const sizes = [
    { label: 'S', value: 's' },
    { label: 'M', value: 'm' },
    { label: 'L', value: 'l' },
    { label: 'XL', value: 'xl' },
    { label: '2XL', value: '2xl' },
    { label: 'FreeSize', value: 'freeSize' },
];

const generateRandomUniqueId = () => {
    return Date.now().toString(36) + '-' + Math.random().toString(36).substr(2);
}

let selectedSizes = [
    { id: generateRandomUniqueId(), value: null, quantity: '' },
];


const validateForm = () => {

    $.validator.addMethod("fileCount", function (value, element, param) {
        return element.files.length <= param;
    }, "Bạn chỉ có thể upload tối đa {0} files.");

    // Thêm phương thức để kiểm tra kiểu file
    $.validator.addMethod("fileType", function (value, element, param) {
        let fileType = element.files;
        for (let i = 0; i < fileType.length; i++) {
            if (!param.includes(fileType[i].type)) {
                return false;
            }
        }
        return true;
    }, "Chỉ chấp nhận file ảnh.");

    $("#add-product-form").validate({
        ignore: [],
        rules: {
            product_sku: {
                required: true,
                minlength: 2
            },
            product_name: {
                required: true,
            },

            product_cal: {
                required: true,
            },
            product_com: {
                required: true,
            },
            ingredient: {
                required: true,
            },
            product_size: {
                required: true,
            },
            product_price: {
                required: true,
                min: 1,
                max: 1000000000,
                number: true
            },
            product_address: {
                required: true,
            },
            product_shipping_method: {
                required: true,
            },
            product_design_file: {
                required: true
            },
            "images[]": {
                required: true,
                fileCount: 10, // giới hạn số lượng file
                fileType: ["image/jpeg", "image/png", "image/gif"] // giới hạn kiểu file
            }
        },
        messages: {
            product_sku: {
                required: "Please enter your username",
                minlength: "Your username must consist of at least 2 characters"
            },
            product_name: {
                required: "Please provide a password",
                minlength: "Your password must be at least 5 characters long"
            },
            messages: {
                "images[]": {
                    required: "Vui lòng chọn ít nhất một file."
                }
            },
        },

        errorPlacement: function (error, element) {
            // error is the error message
            // element is the form element with the error
            // var name = element.attr("name");
            // $("#" + name + "-error").text(error.text());
        },
        showErrors: function (errorMap, errorList) {
            const currTarget = errorList[0];
            if (currTarget) {
                if (currTarget.element.name == "images[]") showImagesErrorMessage(true);
            }
            // Clean previous error messages if any
            $("div[id$='-error']").text("");
            // Default showErrors behavior
            this.defaultShowErrors();
        },

        submitHandler: function (form) {
            let values = {};
            $('#add-product-form .form-control').each(function () {
                const name = $(this).prop('name');
                const val = $(this).val();
                return values = { ...values, [name]: val };
            });
            const fileDesign = document.querySelector('input[name="product_design_file');
            const fileSubmit = document.querySelector('input[name="images[]"]');

            values = {
                id: generateRandomUniqueId(),
                file_design: copyFilesList(fileDesign.files),
                images: copyFilesList(fileSubmit.files),
                sizes: selectedSizes,
                product_quantity: sumQuantity(selectedSizes),
                ...values,
            };


            if (!editId) {
                products.push(values);
                toastr.success('Đã thêm 1 thông tin mặt hàng');
            } else {
                const productIndex = products.findIndex(item => item.id === editId);
                products[productIndex] = values;
                toastr.success('Chỉnh sửa thành công');
            }

            renderProduct();
            $('#add-product-modal').modal('hide');
        }
    });

}

function addValidationSizeRules() {
    $('#add-product-form').find('.size-value').each(function () {
        $(this).rules('add', {
            required: true,
            messages: {
                required: "Hãy chọn Size",
            }
        });
    });

    $('#add-product-form').find('.size-quantity').each(function () {
        $(this).rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: "Hãy nhập số lượng cho Size",

            }
        });
    });
}

const renderSelectSize = (currSize, i) => {
    const sizeSelected = selectedSizes.map(size => size.value);
    const filteredSize = [...sizes].filter(size => !sizeSelected.find(sizeSelected => {
        return (sizeSelected == size.value && size.value != currSize.value);
    }));

    const selectHtml =
        `<select class="form-select form-control size-value" name="size[${i}]value" data-id="${currSize.id}"
        id="">
        <option value="" >Chọn Size</option>
        ${filteredSize.map(size => {
            return `<option value="${size.value}" ${currSize.value == size.value && 'selected'}>${size.label}</option>`;
        })}
    </select>`;

    return selectHtml;
}

const renderFormSizes = () => {
    $('#form-sizes').html(selectedSizes.map((size, i) => {
        return `<div class="mb-3 row align-items-center">
                <div class="col-md-3">
                    <div class="position-relative">
                       ${renderSelectSize(size, i)}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-flat">
                        <input class="form-control size-quantity" type="number" min="1" name="size[${i}]quantity" data-id="${size.id}"
                            id="product_quantity" value="${size.quantity}" placeholder="Nhập số lượng">
                    </div>
                </div>
               ${selectedSizes.length > 1
                ? `<div class="col-md-3">
                        <button class="btn btn-light remove-size-btn" type="button" data-id="${size.id}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                   </div>`
                : ''}
        </div>`
    }));
    
    addValidationSizeRules();
    setTotalQuantity();
}

const canAddSize = () => {
    return selectedSizes.length !== sizes.length;
}
const sumQuantity = (arr) => {
    let quantity = 0;
    arr.forEach(size => quantity += parseInt(size.quantity))
    return quantity;
}
const setTotalQuantity = () => {
    const price = $('input[name="product_price"]').val();
    const totalQuantity = sumQuantity(selectedSizes);
    const total = price * totalQuantity;
    if(total > 0) $('#product_total_price').val(numberToVND(total));
}

const numberToVND = (number) => {
    return number.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}

const copyFilesList = (fileList) => {
    const dataTransfer = new DataTransfer();
    Array.from(fileList).forEach(file => dataTransfer.items.add(file));
    return dataTransfer.files;
}

const renderProduct = () => {
    $('#product-files').empty();
    products.forEach((item, i) => {
        console.log(item)
        const fileDesign = document.createElement('input');
        const fileImages = document.createElement('input');

        $(fileDesign).attr({
            name: `product_files[${i}][file_design]`,
            type: 'file',
            class: 'sr-only'
        });
        $(fileImages).attr({
            name: `product_files[${i}][images][]`,
            type: 'file',
            class: 'sr-only',
            multiple: 'multiple'
        });

        fileDesign.files = item.file_design;
        fileImages.files = item.images;

        $('#product-files').append(fileDesign);
        $('#product-files').append(fileImages);
    });

    const productsRender = [...products];

    $('#product-preview').html(productsRender.reverse().map((item, i) => {  
        let sizeHtml = '';
        item?.sizes?.forEach((size, sizeIndex) => {
            sizeHtml += `<input type="hidden" name="products[${i}][sizes][${sizeIndex}][value]" value="${size?.value}"/>`;
            sizeHtml += `<input type="hidden" name="products[${i}][sizes][${sizeIndex}][quantity]" value="${size?.quantity}"/>`;
        })

        return `<tr>
            <td>
                ${products.length - i}
                <input type="hidden" name="products[${i}][sku]" value="${item?.product_sku}"/>
                <input type="hidden" name="products[${i}][product_name]" value="${item?.product_name}"/>
                <input type="hidden" name="products[${i}][ingredient]" value="${item?.ingredient}"/>
                <input type="hidden" name="products[${i}][cal]" value="${item?.product_cal}"/>
                <input type="hidden" name="products[${i}][price]" value="${item?.product_price}"/>
                <input type="hidden" name="products[${i}][address]" value="${item?.product_address}"/>
                <input type="hidden" name="products[${i}][shipping_method]" value="${item?.product_shipping_method}"/>
                <input type="hidden" name="products[${i}][description]" value="${item?.product_note}"/>
                ${sizeHtml}
            </td>
            <td>
               ${item?.product_sku}
            </td>
            <td>
                ${item?.product_name}
            </td>

            <td class="text-center">
                ${item.product_quantity}
            </td>
            <td>
                <span>${item?.product_cal}</span>
            </td>
            <td>
                ${numberToVND(parseInt(item.product_price))}
            </td>
            <td>
               ${numberToVND(item.product_price * item?.product_quantity)}
            </td>
            <td>
                <a data-id="${item?.id}" type="button" data-bs-toggle="modal" data-bs-target="#add-product-modal" class="edit-product-btn">Xem chi tiết</a>
            </td>
            <td class="text-center">
                <a href="javascript:void(0)" class="text-decoration-none delete-product-item-btn" data-id="${item?.id}"> 
                    <span class="icon-tabler-wrapper icon-sm icon-left"><svg xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-x" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M18 6l-12 12"></path>
                            <path d="M6 6l12 12"></path>
                        </svg>
                    </span>
                </a>
            </td>
        </tr>`;
    }))

    if (products.length > 0) {
        $('.product-card .card-body').show();
    } else $('.product-card .card-body').hide();

}

const resetForm = () => {
    $('#add-product-modal #add-product-form').get(0).reset();
    objectUrls.forEach(url => URL.revokeObjectURL(url));
    editId = null;
    selectedSizes = [
        { id: generateRandomUniqueId(), value: null, quantity: '' },
    ];
    renderFormSizes();
    setShowUploadFile(true);
}


const setShowUploadFile = (condition) => {
    if (condition) {
        $('.gallery-images-wrapper').show();
        $('.change-images-btn-list').hide();
        $('#image-preview-container').html('');
    } else {
        $('.gallery-images-wrapper').hide();
        $('.change-images-btn-list').show();
    }
}

const showImagesErrorMessage = (condition) => {
    if (condition) {
        $('#box-image').addClass('border border-danger');
        $('#images-error-message').show();
    } else {
        $('#box-image').removeClass('border border-danger');
        $('#images-error-message').hide();
    }
}

document.addEventListener("DOMContentLoaded", function () {

    validateForm();
    renderFormSizes();

    $(document).on('hide.bs.modal', '#add-product-modal', function () {
        resetForm();
    });

    $(document)
        .off('change', 'input[name="pick-images[]"]')
        .on('change', 'input[name="pick-images[]"]', function (e) {
            const previewContainer = $('#image-preview-container');
            const fileSubmit = document.querySelector('input[name="images[]"]');
            const dataTransfer = new DataTransfer();

            const addFiles = Array.from(e.currentTarget.files);
            const currFiles = Array.from(fileSubmit.files);
            const mergeArray = [...currFiles, ...addFiles];

            console.log(mergeArray)

            if (mergeArray.length > 10) return toastr.warning('Chỉ được tải tối đa 10 ảnh!');
            if (mergeArray.length > 0) {

                mergeArray.forEach(file => {
                    dataTransfer.items.add(file);
                });
                fileSubmit.files = dataTransfer.files;

                for (var i = 0; i < addFiles.length; i++) {
                    var file = addFiles[i];
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    previewContainer.append(`<div class="position-relative overflow-hidden img-wrapper" style='width: 160px;height: 160px;'>
                        <img src="${url}" class= 'img-thumbnail img-fluid object-fit-cover w-100 h-100'>
                        <button data-name="${file.name}" type="button" class="btn btn-light rounded-circle position-absolute delete-image-btn" style="width:18px;height:32px; top:8px; right:8px;">
                        <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>`);
                }

                showImagesErrorMessage(false);
                setShowUploadFile(false);
            } else {
                setShowUploadFile(true);
            }
        })

    $(document)
        .off('click', '.delete-image-btn')
        .on('click', '.delete-image-btn', function (e) {
            const fileSubmit = document.querySelector('input[name="images[]"]');

            const fileName = $(this).data('name');
            $(`button[data-name="${fileName}"]`).each(function () {
                $(this).parent().remove();
            })

            const remainingFiles = Array.from(fileSubmit.files).filter(file => file.name != fileName);
            const dataTransfer = new DataTransfer();

            remainingFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileSubmit.files = dataTransfer.files;

            if (fileSubmit.files.length > 0) {
                setShowUploadFile(false);
            } else setShowUploadFile(true);
        })


    $(document).on('click', '#reset-image-btn', function () {
        if (confirm('Đồng ý reset ảnh?')) {
            const fileSubmit = document.querySelector('input[name="images[]"]');
            const dataTransfer = new DataTransfer();
            fileSubmit.files = dataTransfer.files;

            setShowUploadFile(true);
            showImagesErrorMessage(false);
        }
    })


    $(document)
        .off('click', '#submit-add-product-btn')
        .on('click', '#submit-add-product-btn', function () {
            $('#add-product-form').trigger('submit');
        });

    $(document).on('click', '.delete-product-item-btn', function () {
        if (confirm('Đồng ý xóa thông tin mặt hàng này?')) {
            const id = $(this).data('id');
            products = products.filter(item => item.id !== id);
            renderProduct();
            toastr.success('Đã xóa 1 thông tin mặt hàng');
        }
    })

    $(document)
        .off('change', 'input[name="product_price"]')
        .on('change', 'input[name="product_price"]', function () {

            setTotalQuantity();
            const price = $('input[name="product_price"]').val();
            const quantity = $('input[name="product_quantity"]').val();
            const total = price * quantity;
        })

    $(document)
        .off('click', '.edit-product-btn')
        .on('click', '.edit-product-btn', function () {
            const form = document.getElementById('add-product-form');

            const id = $(this).data('id');
            editId = id;
            const product = products.find(item => item.id === id);

            console.log(product);

            Object.keys(product).forEach(key => {
                const expectKeys = ['product_design_file', 'images'];

                if (!expectKeys.includes(key)) {
                    const input = form.elements[key];

                    if (input) {
                        input.value = product[key];
                    }
                }
            })

            selectedSizes = product.sizes;
            renderFormSizes();

            if (product.file_design.length > 0) {
                const inputFileDesign = document.querySelector('input[name="product_design_file"');
                inputFileDesign.files = copyFilesList(product.file_design);
            }

            if (product.images.length > 0) {
                const previewContainer = $('#image-preview-container');
                const inputFileImages = document.querySelector('input[name="images[]"');
                inputFileImages.files = copyFilesList(product.images);

                previewContainer.empty();
                for (var i = 0; i < product.images.length; i++) {
                    var file = product.images[i];
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    previewContainer.append(`<div class="position-relative overflow-hidden img-wrapper" style='width: 160px;height: 160px;'>
                        <img src="${url}" class= 'img-thumbnail img-fluid object-fit-cover w-100 h-100'>
                        <button data-name="${file.name}" type="button" class="btn btn-light rounded-circle position-absolute delete-image-btn" style="width:18px;height:32px; top:8px; right:8px;">
                        <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>`);
                }
                setShowUploadFile(false);
            }
            console.log(product)
        })

    $(document).on('click', '.btn-add-size', function () {
        if (canAddSize()) {
            selectedSizes.push({
                id: generateRandomUniqueId(),
                value: null,
                quantity: ''
            })
            renderFormSizes();
        }
    })

    $(document).on('click', '.remove-size-btn', function () {
        const id = $(this).data('id');
        selectedSizes = selectedSizes.filter(size => size.id != id);
        renderFormSizes();
    })

    $(document).on('change', '.size-value', function () {
        const id = $(this).data('id');
        const sizeIndex = selectedSizes.findIndex(size => size.id == id);
        if (sizeIndex !== -1) {
            selectedSizes[sizeIndex].value = $(this).val();
        }
        console.log(selectedSizes)
        renderFormSizes();
    })

    $(document).on('change', '.size-quantity', function () {
        const id = $(this).data('id');
        const sizeIndex = selectedSizes.findIndex(size => size.id == id);
        if (sizeIndex !== -1) {
            selectedSizes[sizeIndex].quantity = +$(this).val();
        }
        console.log(selectedSizes)
        renderFormSizes();
    })

});