/* global GplCart, jQuery */
(function (GplCart, $) {

    "use strict";

    /**
     * Automatically submits step forms
     * @returns {undefined}
     */
    GplCart.onload.installAutosubmit = function () {
        var form = $('form[data-autosubmit="true"]');
        form.append('<div class="status-message">' + GplCart.text('Processing...') + '</div>');
        form.append($('<input>', {type: 'hidden', name: 'next', value: '1'})).submit();
    };
})(GplCart, jQuery);

