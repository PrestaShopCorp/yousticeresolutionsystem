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
<div class="orderDetailWrap"><h1>{$orderName|escape:'htmlall'}</h1>
    <div class="topRightWrap">
        {$orderButton|escape:'false'}
        <span class="space"></span>
        <a class="yrsButton yrsButton-close">x</a>
    </div>
    <h2>{l s=$productsMessage sprintf=$productsMessageCount|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
    {if !empty($products)}
        <table class="orderDetail">

            {foreach from=$products item=product}
                <tr>
                    <td>{$product->getName()|escape:'htmlall'}</td>
                    <td>{$api->getProductReportButtonHtml($product->getHref(), $product->getId(), $product->getOrderId())|escape:'false'}</td>
                </tr>
            {/foreach}
        </table>
    </div>
{/if}