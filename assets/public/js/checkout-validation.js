jQuery(document).ready(function ($) {
    var firstErrorField = null;
    $('p.validate-required').each(function () {
        var inputField = $(this).find('input, select, textarea');
        if (!inputField.val()) {
            inputField.addClass('required-error');
            if (firstErrorField === null) {
                firstErrorField = inputField;
            }
        }
    });

    if (firstErrorField !== null) {
        firstErrorField.focus();
    }

    $('form.checkout input, form.checkout select, form.checkout textarea').on('input change', function () {
        var parentField = $(this).closest('p.validate-required');
        if (parentField.length && !$(this).val()) {
            $(this).addClass('required-error');
        } else {
            $(this).removeClass('required-error').addClass('input-text');
        }
    });
});
