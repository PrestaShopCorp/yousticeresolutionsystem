/**
 * Presta-specific javascript for handling report claims form
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

jQuery(function($) {
    $('form#yReportClaims').submit(function(e) {
	e.preventDefault();
	$.ajax({
	    url: '/modules/yousticeresolutionsystem/index.php?section=getReportClaimsPagePost',
	    type: 'post',
	    dataType: 'json',
	    data: $(this).serialize(),
	    success: function(data) {
		//error occured
		if(data.orderDetail == undefined) {
		    $('form#yReportClaims').find('p').remove();
		    $('form#yReportClaims').append('<p>'+data.error+'</p>');
		}
		//ok, show order detail
		else {
		    $('form#yReportClaims').find('p').remove();
		    $.fancybox({
                        autoDimension: true,
                        content: data.orderDetail,
                        closeBtn: false
                    });
		}
	    },
	    error: function(data) {
		$('form#yReportClaims').find('p').remove();
		$('form#yReportClaims').append('<p>An error occured while sending data, try again later</p>');		
	    }
	});
    });
    
    //hide orderDetail
    $(document).on('click', '.yrsButton-close', function(e) {
        e.preventDefault();
        $.fancybox.close();
    });
});