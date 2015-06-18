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
<a class="yrsButton yrsButton-with-time {$statusClass|escape:'false'}" target="_blank"
   href="{$href|escape:'false'}">
    <span>{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall':'UTF-8'}</span>
    <span>{l s='%d days %d hours' sprintf=[$remainingTimeDays, $remainingTimeHours] mod='yousticeresolutionsystem'}</span>
</a>

{if false}
    {l s='To be implemented'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='Respond to retailer'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='Waiting for decision'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {l s='Escalated to ODR'|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
    {{l s='Waiting for retailer\'s response' mod='yousticeresolutionsystem'}|escape:'htmlall':'UTF-8'}
    {l s="Problem reported"|escape:'htmlall':'UTF-8' mod='yousticeresolutionsystem'}
{/if}