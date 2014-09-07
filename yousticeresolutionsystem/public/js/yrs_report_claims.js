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
	
	$('form#yReportClaims').find('p').remove();
	$('.y-ajax-spinner').remove();
	$(this).append('<div class="y-ajax-spinner"></div>');
	
	$.ajax({
	    url: baseDir + 'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getReportClaimsPagePost',
	    type: 'post',
	    dataType: 'json',
	    data: $(this).serialize(),
	    success: function(data) {
		$('.y-ajax-spinner').remove();
		//error occured
		if(data.orderDetail == undefined) {
		    $('form#yReportClaims').find('p').remove();
		    $('form#yReportClaims').append('<p>'+data.error+'</p>');
		}
		//ok, show order detail
		else {
		    $.fancybox({
                        autoDimension: true,
                        content: data.orderDetail,
                        closeBtn: false
                    });
		}
	    },
	    error: function(data) {
		$('form#yReportClaims').find('p').remove();
		$('.y-ajax-spinner').remove();
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