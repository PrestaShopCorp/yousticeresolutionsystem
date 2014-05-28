/**
 * Presta-specific javascript for handling order detail
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

jQuery(function($) {

    table = $('#center_column').find('.order_qte_input').parents('table');
    if (table.length) {
        var products_ids = new Array();
        $(table).find('tbody tr.item').each(function() {
            id_order_detail = $(this).find('.order_qte_input').attr('name').replace("order_qte_input", "").replace("[", "").replace("]", "").replace(" ", "");
            id_order = $('body').find('input[name=id_order]').val();
            products_ids.push(id_order_detail);
            $(this).find('td:first').append('<div id="yrs_id_' + id_order + '-' + id_order_detail + '"></div>');
        });
        $.get('modules/yousticeresolutionsystem/index.php', {"section": "getProductsButtons", "order_id": id_order, "products_ids": products_ids}, function(data) {
            for (key in data) {
                $('#yrs_id_' + id_order + '-' + key).html(data[key]);
            }
        }, 'json');
    }
});
