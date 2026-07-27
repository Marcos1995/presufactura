(function ($) {
    'use strict';

    function formatMoney(n) {
        return n.toFixed(2).replace('.', ',') + ' €';
    }

    function calcLine($row) {
        var qty = parseFloat($row.find('.line-qty').val()) || 0;
        var price = parseFloat($row.find('.line-price').val()) || 0;
        var vat = parseFloat($row.find('.line-vat').val()) || 0;
        var subtotal = qty * price;
        var lineVat = subtotal * (vat / 100);
        var total = subtotal + lineVat;
        $row.find('.line-total').text(formatMoney(total));
        return { subtotal: subtotal, vat: lineVat };
    }

    function reindexRows() {
        $('#line-items-body .line-item-row').each(function (i) {
            $(this).find('input').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/lines\[\d+\]/, 'lines[' + i + ']'));
                }
            });
        });
    }

    function updateTotals() {
        var subtotal = 0;
        var vat = 0;
        $('#line-items-body .line-item-row').each(function () {
            var line = calcLine($(this));
            subtotal += line.subtotal;
            vat += line.vat;
        });
        $('#total-subtotal').text(formatMoney(subtotal));
        $('#total-vat').text(formatMoney(vat));
        $('#total-grand').text(formatMoney(subtotal + vat));
    }

    function bindRowEvents($row) {
        $row.find('input').on('input change', updateTotals);
        $row.find('.remove-line').on('click', function () {
            if ($('#line-items-body .line-item-row').length <= 1) return;
            $row.remove();
            reindexRows();
            updateTotals();
        });
    }

    $(function () {
        $('#line-items-body .line-item-row').each(function () {
            bindRowEvents($(this));
        });

        $('#add-line').on('click', function () {
            var index = $('#line-items-body .line-item-row').length;
            var html = $('#line-item-template').html().replace(/__INDEX__/g, index);
            var $row = $(html);
            $('#line-items-body').append($row);
            bindRowEvents($row);
            updateTotals();
        });

        updateTotals();
    });
})(jQuery);
