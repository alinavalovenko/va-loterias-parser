jQuery(window).ready(
    jQuery('#lxp-submit-form').submit(event, function () {
        event.preventDefault();
        console.log('Clicked caught');
    }),
);