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

<div class="orderDetailWrap ordersPageWrap"><h1>{l s='Report claims on'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'} {$shopName|escape:'htmlall':'UTF-8'}</h1>
    <div class="topRightWrap">
        {$webReportButton|escape:'false'}
        <span class="space"></span>
        <a class="yrsButton yrsButton-close">x</a>
    </div>
    <h2>{l s='Your orders (%d)' sprintf=$ordersCount|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}</h2>
    {if !empty($orders)}
        <table class="orderDetail">

            {foreach from=$orders item=order}
                <tr>
                    <td>
                        <b>{$order->getName()|escape:'htmlall':'UTF-8'}</b> 
                        ({l s=$order->getPaymentState()|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}, 
                        {l s=$order->getDeliveryState()|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'})<br>
                        {l s='Order date'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}: {date($orderDateFormat, strtotime($order->getOrderDate()))}<br>
                        {l s='Total'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}: {$order->getPrice()} {$order->getCurrency()}
                    </td>
                    <td>{$api->getOrderDetailButtonHtml($order->getOrderDetailHref(), $order)|escape:'false'}</td>
                </tr>
            {/foreach}
        </table>
    {/if}
</div>
    
{if false}
    {l s='paid'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='unpaid'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='delivered'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='undelivered'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
{/if}