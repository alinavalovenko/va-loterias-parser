jQuery(window).ready(function($) {
    var settingsForm = $('#lxp-submit-form');

    settingsForm.submit(event, function () {
        event.preventDefault();
        console.log(settingsForm);
        var domain =  settingsForm.find('input[name="loterias-xml-parser_option[lxp-domain]"]').val();
        var langID =  settingsForm.find('input[name="loterias-xml-parser_option[lxp-language-id]"]').val();
        var affID =  settingsForm.find('input[name="loterias-xml-parser_option[lxp-tl-aff-id]"]').val();
        var chan =  settingsForm.find('input[name="loterias-xml-parser_option[lxp-chan-id]"]').val();
        var data = {
            action: 'lpx_save_options',
            domain: domain,
            langid: langID,
            affid: affID,
            chan: chan
        };

        $.post(lxpObject.url, data, function (response) {
            console.log(response);
        });
    });
});