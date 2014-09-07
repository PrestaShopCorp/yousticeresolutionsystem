/**
 * Presta-specific javascript for handling order detail
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

var yousticeShowingButtons = false;

function showAjaxSpinner(where) {
    $(where).append('<div class="y-ajax-spinner"></div>');
}

function removeAjaxSpinner(where) {
    $(where).find('.y-ajax-spinner').remove();
}

function showProductsButtons() {
    if (!yousticeShowingButtons)
	return;

    var table = $('#center_column').find('.order_qte_input').parents('table');

    if (table.length) {
	var products_ids = new Array();

	$(table).find('tbody tr.item').each(function() {
	    id_order_detail = $(this).find('.order_qte_input').attr('name').replace("order_qte_input", "").replace("[", "").replace("]", "").replace(" ", "");
	    id_order = $('body').find('input[name=id_order]').val();
	    products_ids.push(id_order_detail);

	    $(this).find('td:first').append('<div id="y-id-' + id_order + '-' + id_order_detail + '"></div>');
	    showAjaxSpinner('#y-id-' + id_order + '-' + id_order_detail);
	});
	$.get(baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getProductsButtons', {"order_id": id_order, "products_ids": products_ids}, function(data) {
	    for (key in data) {
		removeAjaxSpinner('#y-id-' + id_order + '-' + key);
		$('#y-id-' + id_order + '-' + key).html(data[key]);
	    }
	}, 'json');
    }
}

jQuery(function($) {
    //in iframe
    if (window.location != window.parent.location) {
	return;
    }

    //show logoWidget
    $('.yousticeShowLogoWidget').click(function() {
	if ($('#yousticeLogoWidget').length)
	    return;

	$.get(baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getLogoWidget', function(data) {
	    $('body').append(data);
	});
    });

    if ($('body').attr('id') !== 'history') {
	return;
    }

    //button to start showing buttons
    $('#center_column h1:first').after('<div id="y-main" \>');
    showAjaxSpinner('#y-main');
    $.get(baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getShowButtonsHtml', function(data) {
	removeAjaxSpinner('#y-main');
	$('#y-main').append(data);

	if ($(data).data('has-reports'))
	    showButtons();
    });

    //start showing buttons
    $(document).on('click', 'a.yrsShowButtons', function(e) {
	e.preventDefault();
	showButtons();
    });

    function showButtons() {
	yousticeShowingButtons = true;
	$('a.yrsShowButtons').remove();
	showAjaxSpinner('#y-main');

	//load web report button
	$.get(baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getWebReportButton', function(data) {
	    removeAjaxSpinner('#y-main');
	    $('#y-main').after(data);
	});

	showOrdersButtons();
	//try to show products buttons
	showProductsButtons();
    }

    function showOrdersButtons() {

	if ($('#order-list').length) {

	    var order_ids = new Array();
	    $('#order-list tbody tr').each(function() {
		var order_href = $(this).find('.history_detail a:last').attr('href');
		var order_id = order_href.split('id_order=')[1];
		order_ids.push(order_id);
		$this = $(this).find('td.history_link');
		$('#report-order-' + order_id).remove();
		$this.append('<div id="report-order-' + order_id + '" style="min-width:230px;min-height: 36px;"></div>');
		showAjaxSpinner('#report-order-' + order_id);
	    });

	    $.get(baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getOrdersButtons', {"order_ids": order_ids}, function(data) {
		for (key in data) {
		    $('#report-order-' + key).html(data[key]);
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
    }

    //reload orderDetail
    $(document).on('click', '.yrsButton:not(.yrsButton-order-detail):not(.yrsOrderDetailButton)'
	    + ':not(.yrsButton-plus):not(.yrsButton-close):not(.yrsShowButtons)', function(e) {
		setTimeout(function() {
		    window.location.reload();
		}, 300);
	    });

    //hide orderDetail
    $(document).on('click', '.yrsButton-close', function(e) {
	e.preventDefault();
	$.fancybox.close();
    });
});
