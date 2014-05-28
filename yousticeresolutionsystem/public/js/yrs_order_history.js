/**
 * Presta-specific javascript for handling order detail
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

jQuery(function($) {
    //in iframe
    if(window.location != window.parent.location) {
        return;
    }

    $('<div/>', {id: 'alertButton'}).prependTo('body');
    $('#alertButton').load('modules/yousticeresolutionsystem/index.php?section=logoWidget', function(data) {
        $(this).html(data);
    });

    if ($('body').attr('id') !== 'history') {
        return;
    }

    $('#center_column h1:first').after('<span id="yrs_reportweb"></span>');
    $('#yrs_reportweb').load('modules/yousticeresolutionsystem/index.php?section=getWebReportButton', function() {

    });

    if ($('#order-list').length) {

        var order_ids = new Array();
        $('#order-list tbody tr').each(function() {
            var order_href = $(this).find('.history_detail a:last').attr('href');
            var order_id = order_href.split('id_order=')[1];
            order_ids.push(order_id);
            $this = $(this).find('td.history_link');
            $('#report_order_' + order_id).remove();
            $this.append('<div id="report_order_' + order_id + '" style="min-width:230px;min-height: 30px;"></div>');
        });

        $.get('modules/yousticeresolutionsystem/index.php', {"section": "getOrdersButtons", "order_ids": order_ids}, function(data) {
            for (key in data) {
                $('#report_order_' + key).html(data[key]);
            }

            $('.yrsButton-plus, .yrsOrderDetailButton, .yrsButton-order-detail').click(function(e) {
                $this = $(this);
                $.fancybox({
                    autoDimension: true,
                    href: $this.attr('href'),
                    type: 'ajax',
                    closeBtn: false
                });
                return false;
            });
        }, 'json');
    }

    //reload orderDetail
    $(document).on('click', '.yrsButton:not(.yrsButton-order-detail):not(.yrsOrderDetailButton)'
        +':not(.yrsButton-plus):not(.yrsButton-close)', function(e) {
        setTimeout(function(){window.location.reload();}, 300);
    });

    //hide orderDetail
    $(document).on('click', '.yrsButton-close', function(e) {
        e.preventDefault();
        $.fancybox.close();
    });
});
