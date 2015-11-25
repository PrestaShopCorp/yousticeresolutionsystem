{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="yContainer">
    <div class="logoLeft">
        <a href="//youstice.com" target="_blank">
            <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/logo.png"/>
        </a>
    </div>
    <div class="logoRight {if $languageCode == 'fr'}fr{/if}">
        <ul>
            <li>{l s='One tool to manage all complaints'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</li>
            <li>{l s='Comes in 10 language versions'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</li>
            <li>{l s='Risk-free trial period'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</li>
        </ul>
    </div>

    <div class="clear"></div>

    <div class="loginInfo">
        <p>{l s='Already have a Youstice account?'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
            <label for="haveAccountNo">
                <input type="radio" name="have_account" id="haveAccountNo" value="0" checked="checked">
                {l s='No'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
            </label>
            <label for="haveAccountYes">
                <input type="radio" name="have_account" id="haveAccountYes" value="1">
                {l s='Yes'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
            </label>
        </p>
    </div>

    <div class="yBlock yInfo">
        {if $languageCode == 'fr'}
            <h2>OPTEZ POUR UN SERVICE CLIENT D'EXCELLENCE</br> AVEC YOUSTICE, MANAGEZ LA SATISFACTION CLIENT</h2>
            <p>&nbsp;</p>
        {else}
            <h2>{l s='SAY GOOD-BYE TO UNHAPPY CUSTOMERS WITH YOUSTICE.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
            <h3>{l s='SAY HELLO TO GREAT CUSTOMER CARE!'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h3>
        {/if}
        <p>{l s='Thank you for downloading the Youstice module! It will help your business manage customer complaints and deal with unresolved disputes.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>

        <p>{l s='Youstice is designed to resolve customer disputes in a quick, emotion-free way, avoiding lengthy conversations or negative sentiment across social networks. Shoppers express their issue and propose a solution, choosing from options directly through the application. Your staff then responds and negotiates a win-win resolution, collecting valuable insight on how to improve the quality of your services.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>

        {if $languageCode == 'fr'}
            <div class="col col-50 no-bottom-margin">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/girl-fr.jpg"/>
                <h2>{l s='RESOLVE CUSTOMER COMPLAINTS WITH YOUSTICE'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
            </div>
            <div class="col col-50 no-bottom-margin">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/notebook-fr.jpg"/>
                <h2>COUVREZ LES COÛTS DE MÉDIATION AVEC YOUSTICE</h2>
            </div>

            <div class="clear"></div>

            <div class="col col-50">
                <p>
                    <a href="//youstice.com" target="_blank">Youstice</a> 
                    {l s='is a web application. It`s a cloud service, helping retailers to manage all consumer complaints and to resolve them in a fast and safe way. You can test our solution during three-month, risk-free trial period.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </p>
            </div>
            <div class="col col-50 no-right-margin">
                <p>
                    Au-delà du service client, Youstice permet l'accès intégré aux services de médiation  pour les litiges non résolus*. Les commerçants gèreront les litiges sur une interface unique. En offrant le recours à la médiation, vous respectez aussi les obligations de 
                    <a href="{l s='eu_consumer_disputes_paper'|escape:'url' mod='yousticeresolutionsystem'}" target="_blank">l'Ordonnace sur la Consommation</a>.
                </p>
            </div>

            <div class="clear"></div>

            <div class="col col-50">
                <a class="yButton" href="{l s='//www.youtube.com/watch?v=QfNrPR6zWfs' mod='yousticeresolutionsystem'}" target="_blank">{l s='WATCH VIDEO'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
            </div>
            
            <div class="col col-50 no-right-margin">
                <a class="yButton" href="{l s='//www.youstice.com/en/pricing' mod='yousticeresolutionsystem'}" target="_blank">{l s='CHECK THE PRICING'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
            </div>

            <div class="clear"></div>

            <p>*en accord avec les conditions du contrat</p>
        {else}
            <div class="col col-50 no-bottom-margin">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/notebook.jpg"/>
                <h2>{l s='RESOLVE CUSTOMER COMPLAINTS WITH YOUSTICE'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
            </div>
            <div class="col col-50 no-bottom-margin">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/ratings.jpg"/>
                <h2>{l s='ESCALATE ISSUES TO OUT-OF-COURT BODIES'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
            </div>

            <div class="col col-50">
                <p>
                    <a href="//youstice.com" target="_blank">Youstice</a> 
                    {l s='is a web application. It`s a cloud service, helping retailers to manage all consumer complaints and to resolve them in a fast and safe way. You can test our solution during three-month, risk-free trial period.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </p>
            </div>
            <div class="col col-50">
                <p>
                    {l s='If a customer disagrees with your decision, they can choose ODR* escalation. By allowing your customers to do so, you become compliant with the'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}                    
                    <a href="//www.youstice.com/images/yousticeimg/media/PDF/Whitepaper_May.pdf" target="_blank">{l s='new EU law on consumer disputes'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>&nbsp;&#8209; 
                    {l s='and prove your status of a consumer-friendly business.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </p>
            </div>

            <div class="col col-50">
                <a class="yButton" href="{l s='//www.youtube.com/watch?v=QfNrPR6zWfs' mod='yousticeresolutionsystem'}" target="_blank">
                    {l s='WATCH VIDEO'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </a>
            </div>

            <div class="col col-50">
                <a class="yButton" href="{l s='//www.youstice.com/en/pricing' mod='yousticeresolutionsystem'}" target="_blank">
                    {l s='CHECK THE PRICING'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </a>
            </div>

            <div class="clear"></div>

            <p>{l s='* ODR (Online Dispute Resolution) is a modern way to resolve disputes online. If a buyer cannot reach a satisfactory agreement with a seller, he/she can ask an ODR provider for an independent decision using Youstice. The ODR provider will appoint a neutral who will make a decision.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>

        {/if}
    </div>

    <div class="yBlock registration">
        <hr>

        <form>
            <h2>{l s='Register'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>

            <div class="col">
                <label for="registration-company">{l s='Company name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="text" id="registration-company" name="company_name" 
                       value="{$shopName|escape:'htmlall':'UTF-8'}" 
                       placeholder="{l s='Company name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="1">
            </div>
            <div class="col">
                <label for="registration-email">{l s='Email'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="email" id="registration-email" name="email" 
                       value="{$shopMail|escape:'htmlall':'UTF-8'}" 
                       placeholder="{l s='Email'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="2">
            </div>
            <div class="col col-right">
                <label for="registration-password">{l s='Password'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="password" id="registration-password" name="password" 
                       value="" 
                       placeholder="{l s='Password'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="{if $languageCode == 'fr'}8{else}7{/if}">
            </div>

            <div class="clear"></div>

            <div class="col">
                <label for="registration-first-name">{l s='First name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="text" id="registration-first-name" name="first_name" 
                       value="{$adminFirstName|escape:'htmlall':'UTF-8'}" 
                       placeholder="{l s='First name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="3">
            </div>
            <div class="col">
                <label for="registration-last-name">{l s='Last name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="text" id="registration-last-name" name="last_name" 
                       value="{$adminLastName|escape:'htmlall':'UTF-8'}" 
                       placeholder="{l s='Last name'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="4">
            </div>
            <div class="col col-right">
                <label for="registration-verify-password">{l s='Verify Password'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="password" id="registration-verify-password" name="verify_password" 
                       value="" 
                       placeholder="{l s='Verify Password'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="{if $languageCode == 'fr'}9{else}8{/if}">
            </div>

            <div class="clear"></div>

            <div class="col col-wider">
                <label for="registration-shop-url">{l s='Shop URL'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</label>
                <input type="url" id="registration-shop-url" name="shop_url" 
                       value="{$shopURL|escape:'htmlall':'UTF-8'}" 
                       placeholder="{l s='Shop URL'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}"
                       tabindex="5">
            </div>
            <div class="col col-right">
                <label for="registration-submit">&nbsp;</label>
                <a class="yButton-2 save" href="#" tabindex="{if $languageCode == 'fr'}10{else}9{/if}">{l s='Register'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
            </div>

            <div class="clear"></div>

            <div class="footer">
                <p>{l s='Registration is free, with no obligations. You may cancel your account at any time during the Trial Period.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>
                <label>
                    <input type="checkbox" name="terms_and_conditions" tabindex="6">
                    {l s='I accept'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    <a href="{l s='//www.youstice.com/en/terms-of-use' mod='yousticeresolutionsystem'}" target="_blank">{l s='Terms & Conditions'|escape:'false' mod='yousticeresolutionsystem'}</a> 
                    {l s='and'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    <a href="{l s='//www.youstice.com/en/privacy-policy' mod='yousticeresolutionsystem'}" target="_blank">
                        {l s='Privacy Policy'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    </a>
                </label>
                {if $languageCode == 'fr'}
                    <label>
                        <input type="checkbox" name="company_is_in_france" tabindex="7">
                        {l s='accept_compliance_package_text_french'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    </label>
                    <input type="hidden" name="is_french_form" value="1">
                {/if}
            </div>
        </form>
        <div class="clear"></div>
    </div>            

    <div class="yBlock features">
        <hr>
        <h2><span>{l s='THIS PLUGIN ADDS THE FOLOWING FEATURES'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</span></h2>

        <div class="col col-50">
            <a href="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}1.jpg" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}1.jpg">
            </a>            
        </div>
        <div class="col col-50 bg">
            <span class="number">1</span>
            <span class="arrow"></span>
            <div class="content">
                <h4>{l s='CUSTOMER\'S ORDER HISTORY'|escape:'false' mod='yousticeresolutionsystem'}</h4>
                <p>{l s='The Youstice button appears in your customer\'s order history'|escape:'false' mod='yousticeresolutionsystem'}</p>
            </div>
        </div>

        <div class="clear"></div>

        <div class="col col-50 bg bg-left">
            <span class="number">2</span>
            <span class="arrow"></span>
            <div class="content">
                <h4>{l s='SPECIFY ITEM'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h4>
                <p>{l s='Your customer raises an issue with a specific item in the order list'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>           
            </div>
        </div>
        <div class="col col-50">
            <a href="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}2.jpg" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}2.jpg">
            </a>            
        </div>

        <div class="clear"></div>

        <div class="col col-50">
            <a href="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}3.jpg" target="_blank" rel="screenshot">
                <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/screenshot-{if $languageCode == 'fr'}fr-{/if}3.jpg">
            </a>            
        </div>
        <div class="col col-50 bg bg-last">
            <span class="number">3</span>
            <span class="arrow"></span>
            <div class="content">
                <h4>{l s='NEW BUTTON'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h4>
                <p>{l s='Customers can also use this button to report general problems'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>           
            </div>
        </div>

        <div class="clear"></div>
    </div>

    <div class="yConfiguration">
        <hr class="dark">

        <h2>{l s='CONFIGURE YOUSTICE FOR YOUR WEBSITE'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
        <form action="{$saveHref|escape:'false'}" method="post" class="saveApiKey">

            <div class="col col-50" id="help-popup-col-1">
                <label for="useSandbox">
                    {l s='Integration with the'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    <a href="//app.youstice.com/blox-odr/generix/odr" target="_blank">
                        {l s='Live'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    </a>
                    {l s='or'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    <a href="//app-sand.youstice.com/blox-odr13/generix/odr" target="_blank">
                        {l s='Sandbox'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    </a>
                    
                    {l s='environment?'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}

                    <a href="#help-popup-1" class="help">?</a>
                    <div class="help-popup" id="help-popup-1">
                        <div class="header">
                            {l s='Info'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                            <a href="#help-popup-col-1" class="close">x</a>
                        </div>
                        <span class="arrow"></span>
                        <div class="content">
                            <p>{l s='For testing purposes, please use Sandbox. Please note there are different API keys for Sandbox and Live environments.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>
                        </div>
                        <div class="bottom"></div>
                    </div>
                </label>
            </div>

            <div class="col col-50">
                <select id="useSandbox" name="use_sandbox">
                    <option{if $use_sandbox == 1} selected{/if} value="1">{l s='Sandbox'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</option>
                    <option{if $use_sandbox != 1} selected{/if} value="0">{l s='Live'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</option>
                </select>
            </div>

            <div class="clear"></div>

            <div class="col col-50" id="help-popup-col-2">
                <label for="apiKey">
                    {l s='Api key'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    {l s='of your shop'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                    
                    <a href="#help-popup-2" class="help">?</a>
                    <div class="help-popup" id="help-popup-2">
                        <div class="header">
                            {l s='Info'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                            <a href="#help-popup-col-2" class="close">x</a>
                        </div>
                        <span class="arrow"></span>
                        <div class="content">
                            <p>{l s='Your API key can be found in the application. Log in (Live or Sandbox), go to Shops menu, click on your Shop. The API Key will be displayed at the bottom of the page.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>
                        </div>
                        <div class="bottom"></div>
                    </div>
                </label>
            </div>

            <div class="col col-50">
                <input id="apiKey" type="text" name="api_key" value="{$api_key|escape:'htmlall':'UTF-8'}">
                <a class="yButton-3" href="#" id="yGetApiKey">{l s='Get your API key'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
            </div>
            
            <div class="col col-50">
                <label for="showLogoWidget">
                    {l s='Tell your Customers about Youstice'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                </label>
            </div>

            <div class="col col-50">                
                <select id="showLogoWidget" name="show_logo_widget">
                    <option{if $show_logo_widget == 1} selected{/if} value="1">{l s='Display Floating Widget'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</option>
                    <option{if $show_logo_widget != 1} selected{/if} value="0">{l s='Use the Complaint Link below'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</option>
                </select>
            </div>
            
            <div class="col col-50" id="help-popup-col-3">
                <label for="logoWidgetLeftOffset">
                    {l s='Widget Position on the bottom of the screen'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}

                    <a href="#help-popup-3" class="help">?</a>
                    <div class="help-popup" id="help-popup-3">
                        <div class="header">
                            {l s='Info'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
                            <a href="#help-popup-col-3" class="close">x</a>
                        </div>
                        <span class="arrow"></span>
                        <div class="content">
                            <p>{l s='A clickable image as shown below will appear on the bottom of the screen leading your customers to a page for submitting complaints.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</p>
                            <img src="{$modulePath|escape:'htmlall':'UTF-8'}views/img/logoWidget.png">
                        </div>
                        <div class="bottom"></div>
                    </div>
                </label>
            </div>

            <div class="col col-50">
                <input id="logoWidgetLeftOffset" type="range" name="logo_widget_left_offset" value="{$logo_widget_left_offset|escape:'htmlall':'UTF-8'}" min="0" max="100">
                <p class="inputRangeLegend">
                    <span>{l s='Left'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</span>
                    <span>{l s='Right'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</span>
                </p>
            </div>

            <div class="col col-50">
                &nbsp;
            </div>

            <div class="col col-50">
                <a class="yButton-3 saveApiKey" href="#">{l s='Save'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
            </div>

            <a href="http://support.youstice.com" target="_blank">{l s='Need help?'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</a>
        </form>
    </div>

    <div class="yConfiguration">
        <hr class="dark">

        <h2>{l s='COMPLAINT LINK'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
        <p>
            {l s='If you want to allow customers to make a complaint, embed the link shown below anywhere on your website, e.g. Contact Form, Customer Care Section, Return Policy, Order Confirmation Email(s) and similar.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
        </p>
        <p>
            {l s='Feel free to use this link also on social networks – Facebook, Twitter or Google+. It will help you answer all negative feedback directly via Youstice`s safe environment.' mod='yousticeresolutionsystem'}
        </p>

        <input id="reportClaimsPageLink" type="text" name="anonymous_report" onclick="select()" value="{$reportClaimsPageLink|escape:'htmlall':'UTF-8'}">
        <div class="clear"></div>
    </div>
</div>
<div id="adminLogoWidget"></div>

<link href="{$modulePath|escape:'htmlall':'UTF-8'}views/css/admin.css" rel="stylesheet" type="text/css" media="all" />
<script src="{$modulePath|escape:'htmlall':'UTF-8'}views/js/admin.js" type="text/javascript"></script>
<script type="text/javascript">

            var errorMessages = {
                invalid_api_key: "{l s='Invalid API KEY'|escape:'quotes':'UTF-8' mod='yousticeresolutionsystem'}",
                request_failed: "{l s='Remote service unavailable, please try again later'|escape:'quotes':'UTF-8' mod='yousticeresolutionsystem'}",
                terms_not_accepted: "{l s='You need to accept Terms & Conditions and Privacy Policy'|escape:'false':'UTF-8' mod='yousticeresolutionsystem'}",
                company_name_required: "{l s='Company name is required'|escape:'javascript':'UTF-8' mod='yousticeresolutionsystem'}",
                first_name_required: "{l s='First name is required'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
                last_name_required: "{l s='Last name is required'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
                email_invalid: "{l s='Email is in invalid format'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
                shop_url_invalid: "{l s='Shop URL is in invalid format'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
                password_less_than_6_characters: "{l s='Password needs to have 6 characters at least'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
                passwords_do_not_match: "{l s='Passwords do not match'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}",
            };

            var sandUrl = '{$registerMeSandboxUrl|escape:'false'}';
            var liveUrl = '{$registerMeUrl|escape:'false'}';
            var checkApiKeyUrl = '{$checkApiKeyUrl|escape:'false'}';
            var registrationUrl = '{$registrationUrl|escape:'false'}';

            $(document).ready(function() {
    {if strlen(trim($api_key))}
                $('#haveAccountYes').click();
    {/if}

            });
</script>

{if false}
    {l s='Youstice: cURL is not installed, please install it.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='Youstice: PDO is not installed, please install it.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='Youstice: PECL finfo is not installed, please install it.'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
{/if}