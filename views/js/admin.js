/**
 * Javascript file for administration
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

jQuery(document).ready(function($) {

    $('input[name="have_account"]').change(function() {
	changeBlocksVisibility($(this).val() == 1);
    });

    $('select#useSandbox').change(function() {
	changeSandboxText();
    });

    function changeSandboxText() {
	if ($('select#useSandbox').val() == 1) {
	    $('.row.onSandbox').show();
	}
	else {
	    $('.row.onSandbox').hide()
	}
    }

    changeSandboxText();
    changeBlocksVisibility($('input[name="have_account"]').val() == 1);

    $('#yGetApiKey').click(function(e) {
	e.preventDefault();
	var win;
	if ($('#useSandbox').val() == 1) {
	    win = window.open(sandUrl, '_blank');
	}
	else {
	    win = window.open(liveUrl, '_blank');
	}

	win.focus();
    });

    $('.yBlock.screenshots a[rel="screenshotRemote"]').fancybox();
    $('.yBlock.howItWorks a[rel="screenshot"]').fancybox();
    
    jQuery('a.saveApiKey').click(function(e) {
	e.preventDefault();
	saveSettings();
    });
    
    jQuery('.registration a.save').click(function(e) {
	e.preventDefault();
	makeRegistration();
    });
});

function saveSettings() {
    jQuery('.yError').remove();
    
    jQuery.post(checkApiKeyUrl, {api_key: jQuery('#apiKey').val(), use_sandbox: jQuery('#useSandbox').val()},
    function(response) {
	if(response.result == 'request_failed') {
	    showSettingsError(errorMessages.request_failed);
	}
	else if(response.result == false) {
	    showSettingsError(errorMessages.invalid_api_key);
	}
	else {
	    window.location.reload();
	}
    }, 'json');
}

function changeBlocksVisibility(haveAccount) {
    if (haveAccount) {
	jQuery('.yBlock').hide();
	jQuery('.yConfiguration').show();
    }
    else {
	jQuery('.yConfiguration').hide();
	jQuery('.yBlock').show();
    }
}

function showSettingsError(errorText) {
    jQuery('form.saveApiKey').append('<div class="yError">'+errorText+'</div>');
    jQuery('html, body').animate({
	scrollTop: jQuery('.yConfiguration').first().offset().top
    }, 2000);
}

function makeRegistration() {
    jQuery('.yError').remove();

    jQuery.post(registrationUrl, $('.registration form').serialize(),
    function(response) {
	
	if (response.result == true) {
	    window.location.reload();
	}	
	else if (response.result in errorMessages) {
	    showRegistrationError(errorMessages[response.result]);
	}
	else if (response.result == false) {
	    showRegistrationError(errorMessages.request_failed);
	}
	else {
	    showRegistrationError('Unknown error occured');
	}
    }, 'json');
}

function showRegistrationError(errorText) {
    jQuery('.registration form').append('<div class="yError">'+errorText+'</div>');
    jQuery('html, body').animate({
	scrollTop: jQuery('.registration').first().offset().top
    }, 2000);
}