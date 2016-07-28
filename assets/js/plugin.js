/**
 * Meetup Registration Scripts
 */
jQuery(document).ready(function () {
    var $ = jQuery;

    $('#meetup-registration-submit').click(function () {
        var checkboxes = $('.meetup-registration .checkbox-required');
        var inputs = checkboxes.find('input[type="checkbox"]');
        var first = inputs.first()[0];

        inputs.on('change', function () {
            this.setCustomValidity('');
        });

        first.setCustomValidity(checkboxes.find('input:checked').length === 0 ? 'Wählen sie eines der Felder' : '');
    });

});
