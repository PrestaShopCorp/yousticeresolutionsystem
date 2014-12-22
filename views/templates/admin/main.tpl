{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<form class="yContainer" action="{$saveHref|escape:'false'}" method="post">
    <div class="logoLeft">
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTUwMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2r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"/>
        <p>{l s='Resolve customer complaints in a few clicks.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
    </div>
    <div class="logoRight">
		<a href="http://www.youstice.com/?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_weblink&amp;utm_campaign=plugins" class="roundedAnchor" target="_blank">
			<span>{l s='For more information'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
			<span>{l s='about Youstice visit'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
			<span class="anchor">www.youstice.com</span>
		</a>
        <a href="http://support.youstice.com/?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_weblink&amp;utm_campaign=plugins" class="roundedAnchor" target="_blank">
            <span>{l s='Need some help?'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
            <span>{l s='Our support team is here for you'|escape:'htmlall' mod='yousticeresolutionsystem'}.</span>
            <span class="anchor">support.youstice.com</span>
        </a>
    </div>

    <hr>

    <div class="loginInfo">
        <p>{l s='It only takes a few minutes to get started with Youstice.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
        <p>{l s='Already have a Youstice account?'|escape:'htmlall' mod='yousticeresolutionsystem'}
            <input type="radio" name="have_account" id="haveAccountNo" value="0" checked="checked">
            <label for="haveAccountNo">{l s='No'|escape:'htmlall' mod='yousticeresolutionsystem'}</label>
            <input type="radio" name="have_account" id="haveAccountYes" value="1">
            <label for="haveAccountYes">{l s='Yes'|escape:'htmlall' mod='yousticeresolutionsystem'}</label></p>
    </div>

    <div class="yBlock stopScathingReviews">
        <hr>
        <h2>{l s='Stop scathing reviews'|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
        <a href="https://app.youstice.com/blox-odr/generix/odr/en/app2/_subscription_?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_signup&amp;utm_campaign=plugins" target="_blank" class="roundedAnchor centered">{l s='START FREE TRIAL'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
        <div class="left">
            <h3>{l s='Stop scathing reviews. Handle customer complaints fast and right.'|escape:'htmlall' mod='yousticeresolutionsystem'}</h3>
            <p>{l s='It’s a storeowner’s worst nightmare.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
            <p>{l s='A disgruntled customer criticizes your business on social media. You are instantly thrown into crisis mode, trying as best you can to save your reputation.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
            <p>{l s='It matters little whether the criticism is justified. Prevention is the best cure.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>

            <h3>{l s='You can resolve customer complaints effectively. You just need the right tool.'|escape:'htmlall' mod='yousticeresolutionsystem'}</h3>
            <p>{l s='Resolving complaints rapidly and effectively can place a heavy burden on your small business. The longer customers wait for your reply, the angrier they get. Yet you can’t be behind your computer screen 24/7.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
            <p>{l s='Let us take the pain out of handling customer complaints. Thanks to our award-winning app, your customers know that you stand by your products and they trust your business.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
        </div>
        <div class="right">
            <div class="imgHelper"></div>
            <img src="//www.youstice.com/images/yousticeimg/screenshots/laptop_with_coffee.jpg">
        </div>
        <div class="clear"></div>
        <a href="https://app.youstice.com/blox-odr/generix/odr/en/app2/_subscription_?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_signup&amp;utm_campaign=plugins" target="_blank" class="roundedAnchor centered">{l s='START FREE TRIAL'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
    </div>

    <div class="yConfiguration">
        <hr>
        <h2>{l s='Configure Youstice for your website'|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
        <div class="row">
            <label for="useSandbox">{l s='Is the API key for Live or Sandbox environment?'|escape:'htmlall' mod='yousticeresolutionsystem'}</label>
            <select id="useSandbox" name="use_sandbox">
                <option{if $use_sandbox == 1} selected{/if} value="1">{l s='Sandbox'|escape:'htmlall' mod='yousticeresolutionsystem'}</option>
                <option{if $use_sandbox != 1} selected{/if} value="0">{l s='Live'|escape:'htmlall' mod='yousticeresolutionsystem'}</option>
            </select>
        </div>
        <div class="row onSandbox">
            <p>
                {l s='For testing purposes use our Sandbox environment. Please keep in mind that there are different API keys for Sandbox and Live environments. To start using Sandbox and get the matching API key you need to'|escape:'htmlall' mod='yousticeresolutionsystem'}
                <a href="https://app-sand.youstice.com/blox-odr13/generix/odr/{$currentLanguage|escape:'htmlall'}/app2/_shopConfiguration_?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_signup&amp;utm_campaign=plugins" target="_blank">{l s='register specifically for Sandbox'|escape:'htmlall' mod='yousticeresolutionsystem'}.</a>
            </p>
        </div>
        <div class="row">
            <label for="apiKey">{l s='API Key of your shop'|escape:'htmlall' mod='yousticeresolutionsystem'}</label>
            <input id="apiKey" type="text" name="api_key" value="{$api_key|escape:'htmlall'}">
            <a class="roundedAnchor style2" href="#" id="yGetApiKey">{l s='GET YOUR API KEY'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
        </div>
        <div class="row">
            <label></label>
            <a class="roundedAnchor style2 save" href="#">{l s='SAVE'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
        </div>
        <p>
            {l s='Your API key can be found in Youstice application. Log in to Youstice'|escape:'htmlall' mod='yousticeresolutionsystem'}
            (<a href="https://app.youstice.com/blox-odr/generix/odr/{$currentLanguage|escape:'htmlall'}/app2/_shopConfiguration_?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_signup&amp;utm_campaign=plugins" target="_blank">{l s='Live'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
            {l s='or'|escape:'htmlall' mod='yousticeresolutionsystem'}
            <a href="https://app-sand.youstice.com/blox-odr13/generix/odr/{$currentLanguage|escape:'htmlall'}/app2/_shopConfiguration_?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_signup&amp;utm_campaign=plugins" target="_blank">{l s='Sandbox'|escape:'htmlall' mod='yousticeresolutionsystem'}</a>),
            {l s='go to menu SHOPS, click on your shop and see API key on the bottom of the page.'|escape:'htmlall' mod='yousticeresolutionsystem'}
        </p>
        <p class="empty">&nbsp;</p>
        <p>
            <b>{l s='Need some help?'|escape:'htmlall' mod='yousticeresolutionsystem'}</b><br />
            {l s='Our support team is here for you'|escape:'htmlall' mod='yousticeresolutionsystem'}: <a href="http://support.youstice.com/?utm_source=eshop&amp;utm_medium=cpc&amp;utm_content=presta_weblink&amp;utm_campaign=plugins" target="_blank">support.youstice.com</a>
        </p>
    </div>

    <div class="yConfiguration">
        <hr>
        <h2>{l s='Filing a claim without login'|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
        <label for="reportClaimsPageLink"><b>{l s='Optionally, copy & paste the code below to any place on your website if you want to allow your customers to file claims without logging in based on the customer’s email address and order reference.'|escape:'htmlall' mod='yousticeresolutionsystem'}</b></label>
        <p>{l s='Feel free to use this link on social networks. Post it proactively on Facebook, Google+, Twitter, etc. It will help to redirect negative opinions and potential complaints into Youstice and keep your wall clean.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
        <input id="reportClaimsPageLink" type="text" name="anonymous_report" onclick="select()" value="{$reportClaimsPageLink|escape:'htmlall'}">
        <div class="clear"></div>
    </div>

    <div class="yBlock howItWorks">
        <hr>
        <h2>{l s='How the Youstice plugin works'|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
        <p>
            <a href="{$modulePath|escape:'false'}img/screenshot_1.png" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'false'}img/screenshot_1.png">
            </a>
            <span>{l s='New button ‘Would you like to file a complaint?’ appears in an order history of each customer.'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
        </p>
        <p class="right">
            <a href="{$modulePath|escape:'false'}img/screenshot_2.png" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'false'}img/screenshot_2.png">
            </a>
            <span>{l s='The customer can use this option to report a problem, whether related to a specific order or not.'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
        </p>
        <p>
            <a href="{$modulePath|escape:'false'}img/screenshot_3.png" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'false'}img/screenshot_3.png">
            </a>
            <span>{l s='It is also possible to report a problem with a specific item within an order.'|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
        </p>
        <div class="clear"></div>
    </div>

    <div class="yBlock screenshots">
        <hr>
        <h2>{l s='Screenshots'|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
        <a href="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_1.jpg" target="_blank" rel="screenshotRemote">
            <img src="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_small_1.jpg">
        </a>
        <a href="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_2.jpg" targer="_blank" rel="screenshotRemote">
            <img src="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_small_2.jpg">
        </a>
        <a href="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_3.jpg" target="_blank" rel="screenshotRemote">
            <img src="//www.youstice.com/images/yousticeimg/screenshots/app_screenshot_remote_small_3.jpg">
        </a>
        <div class="clear"></div>
    </div>
</form>

<link href="{$modulePath|escape:'false'}css/admin.css" rel="stylesheet" type="text/css" media="all" />
<script src="{$modulePath|escape:'false'}js/admin.js" type="text/javascript"></script>
<script type="text/javascript">
    var sandUrl = 'https://app-sand.youstice.com/blox-odr13/generix/odr/{$currentLanguage|escape:'htmlall'}/app2/_shopConfiguration_?utm_source=eshop&utm_medium=cpc&utm_content=presta_signup&utm_campaign=plugins';
    var liveUrl = 'https://app.youstice.com/blox-odr/generix/odr/{$currentLanguage|escape:'htmlall'}/app2/_shopConfiguration_?utm_source=eshop&utm_medium=cpc&utm_content=presta_signup&utm_campaign=plugins';
    var checkApiKeyUrl = '{$checkApiKeyUrl|escape:'false'}';
    var errorMessagesSelector = '.yConfiguration .error, .yConfiguration .bootstrap';
    {if $is1_5Version}
        var invalidApiKeyHtml = '<div class="error">{l s='Invalid API KEY'|escape:'htmlall' mod='yousticeresolutionsystem'}</div>';
    {else}
        var invalidApiKeyHtml = '<div class="bootstrap"><div class="module_error alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>';
        invalidApiKeyHtml += '{l s='Invalid API KEY'|escape:'htmlall' mod='yousticeresolutionsystem'}</div></div>';
    {/if}
    var requestFailedHtml = '<div class="error">{l s='Remote service unavailable, please try again later'|escape:'htmlall' mod='yousticeresolutionsystem'}</div>';
    $(document).ready(function() {
        {if strlen(trim($api_key))}
            $('#haveAccountYes').click();
        {/if}

    });
</script>