jQuery(window).ready(function ($) {
    var settingsForm = $('#lxp-submit-form');
    var updateDataBtn = $('#lxp-update-data');
    var loaderElem = $('.loader-wrap');
    var successMessage = $('.status-message-success');
    var errorMessage = $('.status-message-error');

    settingsForm.submit(event, function () {
        event.preventDefault();
        var apiurl = settingsForm.find('input[name="loterias-xml-parser_option[lxp-api-url]"]').val();
        var domain = settingsForm.find('input[name="loterias-xml-parser_option[lxp-domain]"]').val();
        var langID = settingsForm.find('input[name="loterias-xml-parser_option[lxp-language-id]"]').val();
        var affID = settingsForm.find('input[name="loterias-xml-parser_option[lxp-tl-aff-id]"]').val();
        var chan = settingsForm.find('input[name="loterias-xml-parser_option[lxp-chan-id]"]').val();
        var data = {
            action: 'lpx_save_options',
            domain: domain,
            langid: langID,
            affid: affID,
            chan: chan,
            apiurl: apiurl
        };

        $.post(lxpObject.url, data, function (response) {
            alert(response);
        });
    });

    updateDataBtn.click(function () {
        loaderStart();
        var data = {
            action: 'lpx_update_date',
        };

        $.post(lxpObject.url, data, function (response) {
            loaderEnd();
            if (response === 'success') {
                successMessage.fadeIn(100);
                setTimeout(function () {
                    successMessage.hide();
                }, 3000);
            } else {
                errorMessage.fadeIn(100);
                setTimeout(function () {
                    errorMessage.hide();
                }, 3000);
            }
        });
    });

    function loaderStart() {
        loaderElem.css('display', 'block');
    }

    function loaderEnd() {
        loaderElem.css('display', 'none');
    }
});