$(document).ready(() => {
    $("#btn-active-discount-amount").on("click", function () {
        $("#input-group-text").text("VNĐ");
        $("#discount_type_input").val("VNĐ");
        $("#btn-active-discount-amount").addClass("active");
        $("#btn-active-discount").removeClass("active");
    });
    $("#btn-active-discount").on("click", function () {
        $("#input-group-text").text("%");
        $("#discount_type_input").val("%");

        $("#btn-active-discount-amount").removeClass("active");
        $("#btn-active-discount").addClass("active");
    });
});
