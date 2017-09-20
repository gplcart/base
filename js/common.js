/* global Gplcart, jQuery */
(function (Gplcart, $) {

    "use strict";

    /**
     * Automatically submits step forms
     * @returns {undefined}
     */
    Gplcart.onload.installAutosubmit = function () {
        var form = $('form[data-autosubmit="true"]');
        form.append('<div class="status-message">' + Gplcart.text('Processing...') + '</div>');
        form.append($('<input>', {type: 'hidden', name: 'next', value: '1'})).submit();
    };
})(Gplcart, jQuery);

