<?php
/**
 * Youstice Resolution Module
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

if (!defined('_PS_VERSION_'))
	exit;

class YousticeResolutionSystem extends Module
{
	private $y_api;

	public function __construct()
	{
		$this->name                   = 'yousticeresolutionsystem';
		$this->tab                    = 'advertising_marketing';
		$this->version                = '1.4.5';
		$this->author                 = 'Youstice';
		$this->need_instance          = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		$this->dependencies			  = array();

		parent::__construct();

		$this->displayName		= $this->l('Youstice Resolution Module');
		$this->description		= $this->l('Your online justice');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		require_once('SDK/Api.php');
		$db = array(
			'driver' => 'mysql',
			'host' => _DB_SERVER_,
			'user' => _DB_USER_,
			'pass' => _DB_PASSWD_,
			'name' => _DB_NAME_,
			'prefix' => _DB_PREFIX_
		);

		$this->y_api = YousticeApi::create();

		$this->y_api->setDbCredentials($db);
		$this->y_api->setLanguage($this->context->language->iso_code);
		$this->y_api->setShopSoftwareType('prestashop');
		$this->y_api->setThisShopSells(Configuration::get('YRS_ITEM_TYPE'));
		$this->y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));
		$this->y_api->setSession(new YousticeProvidersSessionPrestashopProvider());

	}

	public function hookDisplayHeader()
	{
		//if ajax call
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return;

		$this->context->controller->addCSS($this->_path.'public/css/youstice.css', 'all');
		$this->context->controller->addJS($this->_path.'public/js/yrs_order_history.js');

		if (version_compare(_PS_VERSION_, '1.6.0') <= 0)
		{
			$this->context->controller->addCSS($this->_path.'public/css/jquery.fancybox.css', 'all');
			$this->context->controller->addJS($this->_path.'public/js/fancybox/jquery.fancybox.pack.js');
		}

		if (Tools::getValue('section') == 'getReportClaimsPage')
			return $this->addReportClaimsPageMetaTags();
	}

	public function hookDisplayFooter()
	{
		//if ajax call
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return;

		return '<a class="yousticeShowLogoWidget">Youstice - show logo</a>';
	}

	protected function addReportClaimsPageMetaTags()
	{
		$title_string = $this->context->shop->name.' - Youstice - '.$this->l('File a complaint');
		$html = '<meta property="og:title" content="'.$title_string.'" />';
		$html .= '<meta property="og:type" content="website" />';
		$html .= '<meta property="og:url" content="'.$this->getReportClaimsPageLink().'" />';
		$html .= '<meta property="og:image" content="'._PS_BASE_URL_.$this->_path.'logo.png" />';

		$description_text = $this->l->t('In case you want to complain about a product or service, please follow this link.');
		$html .= '<meta property="og:description" content="'.$description_text.'" />';
		return $html;
	}

	public function hookActionOrderDetail()
	{
		echo '<script type="text/javascript" src="'.$this->_path.'public/js/yrs_order_detail.js"></script>';
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$yrs_apikey = (string)Tools::getValue('YRS_API_KEY');
			if (!$yrs_apikey	|| empty($yrs_apikey) || !Validate::isGenericName($yrs_apikey))
				$output .= $this->displayError( $this->l('Invalid API KEY') );
			else
			{
				Configuration::updateValue('YRS_API_KEY', $yrs_apikey);
				$output .= $this->displayConfirmation($this->l('Settings were saved successfully.'));
			}

			$yrs_sandbox = (string)Tools::getValue('YRS_SANDBOX');
			if (!in_array($yrs_sandbox, array(0,1)))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
				Configuration::updateValue('YRS_SANDBOX', $yrs_sandbox);

			$yrs_item_type = (string)Tools::getValue('YRS_ITEM_TYPE');
			if (!$yrs_item_type	|| empty($yrs_item_type) || !Validate::isGenericName($yrs_item_type))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
				Configuration::updateValue('YRS_ITEM_TYPE', $yrs_item_type);

			$this->y_api->setThisShopSells($yrs_item_type);
			$this->y_api->setApiKey($yrs_apikey, $yrs_sandbox);

			$this->y_api->install();

		}

		$image_data  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccl';
		$image_data .= 'lPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+';
		$image_data .= 'IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxM';
		$image_data .= 'i8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YX';
		$image_data .= 'gtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHh';
		$image_data .= 'tbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9i';
		$image_data .= 'ZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc';
		$image_data .= '3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIE';
		$image_data .= 'NTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTU';
		$image_data .= 'wMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJE';
		$image_data .= 'Rj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2rXaqxaLH27faTk5S0crO9j';
		$image_data .= 'LzFkMS9hLx9EnvSrNGampuwZ7B2DHSta6yeXJ3ExMWGIYScVJuLHIircaquYa3MnMu0g7OzfLKzs7Ty6PK7u7zDjMKvY656enzp2emKGYf19fWpYa';
		$image_data .= 'iqqqulXaXNqszJycqMIol8JHqvaq5uDmzo1ujx5vHU1NXQp9ClUKOKiotlAWSdQJrEmsOjo6SnYaepZKj06vTt7e3y8vKDg4Tl0eWfV578+/yJIob';
		$image_data .= '8+fzd3d7+/v6EHoLiyOGUMZLAib+NJIl+Gny9kbz69vqSS5CCMYHhxeCSKo+TLpDewd3Vr9SDFYCjTaHev92OK4zVutWLPYqoXqekaqPp6emdTJt4';
		$image_data .= 'FnaROI/t3e3m1eWxd7D27/baudp2FXSkX6KhXaDgyN+sZKrZwdmNMouZPpeSJ4/exd6ya7CgRp51GnPZ2dnfyd7SsdKAKX/48/j48viOIIt5GHmNQ';
		$image_data .= 'otpBmiQIY3s2uysYKuaOpiQJY3FoMW5fLjYutji0OKCGYBqCGlxEXCmWqWVUJPo0+jhzOC2bLS3ebXWtNWjWKHHk8b+/P6nVKb59vloaGq0aLLZtd';
		$image_data .= 'hwEm6OJItoBGaBHICRJo5uD22KIYfTtdKYNZXIpMdxDHB4EHblzeSzb7KPj5CJFobq3OluCW2ZVZdyFHGTOpFsCmvkyuOSQJDs2OtiYmTz6fOoaae';
		$image_data .= 'iZKG5driGF4N/Hn7ZvtiIHoXXsteHGYSRJI7Il8fcwNzdvdx0FXOPRo59F3uILYakW6JtDGyGhod+foD69Pr59PmJO4eenqB0EHLbw9vOr82pV6ia';
		$image_data .= 'Qpfv4++GN4R4H3e5g7jUt9PIm8iQJo21arOwYK5gAF9rDWqurq/X19f38PeXl5jb29zv7/C3t7inp6j9/f3Pz8/69vm4ibe2dLXg4ODn0ueXSpazb';
		$image_data .= 'LN6GXqIJ4br2+u/v8BgYGJxcXP6+vqINYeCEH/59vrcxdv07PT7+Pt8HXvz6/Lk0uP///9A738jAAAT0ElEQVR42uyaCVgT57rHWRN2BEQIYRGjAc';
		$image_data .= 'KOQlQELUGCsihKhaKouAKHKuJGDQYRtQRcEJGrEAgFoqHAwXoFQQqotUbaYkUqdUUpFqu0Qj3V3mrt3Pebycp2bJ+jR/v4b4mT2TLzy7v8v2+igr2';
		$image_data .= 'VXCpvEbyl8VdoPP32kvtbGhJNqDE393N8SwNXrrV5XFy838dvaeB5ohEfHxdnvv8tDaQva/zi4uPNv34zbuTZrHmgd9tfWt0oNY+P97P44M2goW2c';
		$image_data .= 'RqVS2xa8NBq5O/z8LDzekCDXPqoFon768jrsR+Z+Fs9ex1tnbTuO9KUSDeOXS2OmhV+8+anXkcadh1QajUb+8VXGxiZUN6zZryMNXTwQlGgYk19u3';
		$image_data .= 'bhvbgo4JryeNIwH0Hh2ZjXo8PKXRaPd1NQ03tR8ypsRGy/bb5RWIhqmGqteQxpHXzWNvBo/nIb5R68fjS+OkhAN91dH430IDcTDbyVrhOO/HHtCpr';
		$image_data .= 'F4N9aWrxkra4HaRxZc1ddvnrROZl/YPid8CJ2Q+LtcH6lOzFHsbMdXqzUfaFZbPYFYy4Ljlh81BtHm4XuzsB75kV8oXd2JWaMsDzRbLpjlMbAXjJ2';
		$image_data .= 'HtqitXt7zYjSsgEZNqDkExx8j0NicM817mkRx69CaDu+EGvQuoSaBMYrY6Q/90YysLAYDXhIMTs8kQu8ByZWQ/STJBSYZa7m6JiW5aiW8Kzv/lgXZ';
		$image_data .= 'NDI5jZpGJlNPTvJBI2vjpKQkLURDK+khSGsbpv0AXwLRDssvjX1s3ENoMzTqjRs3Dhkc3qJw1cfGXb9BiGYw70VoTNhtGl+54zlU0srQEWiMMo+Hu';
		$image_data .= '2TExcUx4vxwGtZZDO9oXN45OI12vegse4QHaVp0VvYRnMZR+4Q0fFWWGnEqDxI1gQb/kUhZ70lPv+cBmWYsFY18dBaGuaeRSCQoG2gNCf6oxzHth9';
		$image_data .= 'KdyHK/sTyIStMidOjQIdqNk8dkcTEO8Bw6dP369X37rt9o2+nx72lYiK8kP3HHrMBz1D4dgYYfgIjTYMRpaGjk4DR2MrylwmmsS8BZSHEkJEQzJkH';
		$image_data .= 'c5unaJ5AS4NZJ0TIaUpGlseFOSiMZK4hG3YO5UxXXaGnRjkt8KFKajMa7WlQtuXAekihw170BGPZdP0Rr81y/ffZ8yuR2JRqnTHeH5g0Yzl8RC7hb';
		$image_data .= 'e7BvzyY/ebJjIIN/TJnyIbHkuNTvPOLB0DifY47fxEq/LIIFIyvSEcN2MRj2OA17CBYJj6zmHizPFaIHvZHTSCOnpRE0JLExJzsNjwBIEzKBJS0Ic';
		$image_data .= '0cuCwdhTIIlKhloaFElcSCjsZoqAWRMoxkTPGj7fPCY2Uc7dKMtcnJ4E4dOT20qSkykRH6hQMORy+TfrFQaHm8J5ednckthKaw6syLMI0+p2Diagx';
		$image_data .= 'bii6esrVaex6NjZYf1O2jNFKuOGgQj2qLD+hS2l+Ftj8RguFrv1PXOmiZNjmehHXoGrig8pDSeBhkEGSjRmEdGSUEijVtwZn9zEsoG2oPcdoOgIAM';
		$image_data .= 'iMDqCkNSxLfrwz0NFGkcIGMZU45MGBieNqVoPH+7bd+MMbPE5WFISnlhET9RSO6xGS21KBJUHyWnMCeMLBG78HR++v7k01MpiceXcr8JcqgSZ4mRU';
		$image_data .= 'ePT4VVWZLmFffVZZa2Glt+PSrn+s2GRuGqcRXyMtS2M1UNlQmDJ8WhOn4e19HrmBp6NxGt5ZQZtW9bDzJlydhsdDAgOvHaxse4gNad1A8jFOUKBhS';
		$image_data .= 'cbhzCLqgC4N0cBjeDkeKJA2cs15gNJGQkNbF4VKEk1LbVteLnvO8klpbW2R68P1YdPJ8qb0okSKJ9GdP52PcDSFt8totFeJBYBDfIvHZyLx+VxetZ';
		$image_data .= 'sgk2mN18ANAsAhKBNXVyCJy6piU/pcksOWLVohvZDQHA2NuPPyRrzLD4rI+excWLyaA1FiH+29S7rNXRfHEZ2NujHrgX2CPDbwluiqSGMcSpw0A0z';
		$image_data .= 'OhkrQ2EbQUHRfXzwwlseGGhVvMCdlPl2N09TUhELAPT09MbGIEym1lJMoKDjoB2Q08pK5iIab2E0glZtYLOYJiBFKLVOQmVlVVXUWFEvoIvqLUZd+';
		$image_data .= '1N6fzHPiNc7LvFDHeQ1vDb/NqBRIKoi8Y2Ieo+1RtmSdRlZiZTRkCkOBxlicRrSEhj6iQd0p8QpqCUlJxtn/NwyNDx5oyTrsCWrbwcj12yeryz91f';
		$image_data .= 'mp6Oh1oGNDh5ouajsjmcNanwgqOJ1tWN3ageODxCBAoPvjcW/kbNiyW3KuLy4Yqt2oel8fNJICUJZeVlfGt5a51WWy9WbKZdEW7hgaqmicIN2IPre';
		$image_data .= 'WqYtGZxUDtJdoApzEwNiQ0JPQm4ZmSJnEtW8B2PX+KX/Y22qBR2wf7yOSDnpPxc12lpxekppZbKoy5ZkO1pI/DZk5GN0+hybcEITxNsz3kPeXS7q3';
		$image_data .= '3P0pmAgtx/uL7oTsuvb/3Y49VUvv2w3P1FZsWOk7Rs/oKcMRmVqz0uL9oa6iCuwut6EtJESZLok8v5PM1y9YgNmxrc0a0t/c0H6WG1IFyxd7VR0qD';
		$image_data .= 'MSyNn3FrQUob92Ouck/bhvwGjfyO4oh+e0F6U0H5OLRMphQVFaVu/1Jxegh03AcSpQjRsHw2Ryo1lCpFqccG+I32uUyoH7zK4QdppVcyq85WbCgdP';
		$image_data .= 'Nqtj+nr6zMhvkPWJ8Jz586ZoETx+bzerL/frEZ57/1+0ajJQLA+zp5GciVFT1Kookm4O5Vm1tU21GBJZHK22hFFt/5jScns2eGc9xRplHDS04voqF';
		$image_data .= 'Bu2Z4KNCi6Q9zCp3iZSNw+WabtCE8i5fBA98Wy4FYLBEyXYWa75izmQfnghm0aYlutSUpK38V4wovEnAsJMXyCLt49BMnkvvLOswL7+/ujAiEi5ix';
		$image_data .= 'ds2xZyZogBa6X14JmS+dsviQ5w12Hh0cVFBevNZCHwjGKs3NBAeXMQBrpOI3l85uKihLpzUNc6FU6TqMpVSa8xSZSFgz2oqX50Gu5t4Z04h9v5WdW';
		$image_data .= 'ZfIrnw+18fTFFFA93qdWmiACHTiYcyGFhYVCtQFhHlhYGBhoqIcKjmpgYKFhtnybewPSr7Ij7jxojGgoLkb33lDnTJK21GPFBQXD09hTkA6xQR81x';
		$image_data .= 'IXqD6JBqPzTIZz53jAoHtXMxXmDTrPQBWCIq61yh561/QThKEMTQ3lrYvpC+kLcJTRCCusH00AiaGSYmZkZ7pRvezejuNjfX1XhiHnRxRENzs7BcP';
		$image_data .= 'fOdcGWL0SjCacxaXgaay9PVtb2Y0ONU55XQvFwY24d+LgxVMzLz+dtGPah7JQKoHHxJ4RNGBLSJzTH165AiVJoojfA1qviNK7iNAKVaRxQ9S8uLo5';
		$image_data .= 'Q4vejwdrGugacR3HdVTmN4GEzJbEJ0dAf4jotcRoF8/IGij30qM2KC62WeeVbxQmB57VMcb6A+c3wQ3uP+tiUlJgUiIiasj5IFALb034IjhBhjvK+';
		$image_data .= 'zUKcxi6CBiwp0MiBYBlIA+rLad3ZEcXBwKPYeQ9BwxnFxntD07hTgldR2hDXuRqvopRJLzzbc2kD4OBfUWwtVjfBmHFNR5oSrKkAGGX3Me3k2L6+m';
		$image_data .= 'M/vEDMMPwlRdASqK+7JXmoICAr91WU0Vsq2vdNghjSQBmo2zeF4eNThRWYWHhv01UPTwNoo0F6awscOvszl89MRDeqLz339sUEs4H+mGBsfQXHlW4';
		$image_data .= 'w4h7buYkxMysVF2KUy6C5l0vvTQxU1RKh06GYEI1B1KfrO/4loqDJk26JVcRqSumGZcDTJ1UB6Ie7hUEyDKetR5TqC143G5mFo6Jeno2WDwZfZ48l';
		$image_data .= 'B9qLg2AvT8AAaTCulabMr1QKm9Yg02J8AjhjhilqgkRK7VzplFIJSJcRQod78/Pk56CiBeGllX4YS0lC4RmqTmgkYUho0k4yIX9fKjsyuQzT+iXZW';
		$image_data .= 'v4bipGF9rhKNggIJjW3XCoBGk7Ni7GB7krRlLZazXlthyw9nRqDhyBS4cd9XWjWXL+B+1TMijikVQKOs40ksqqayXUeXIRrnzsm8+bo1htB0C1WX4';
		$image_data .= 'QTOE3FCGI4e/cJAMzxVVIm8Torw92+YLcvPo0DDuWEtGu7NvNwQDGWkcWeuMo2CcqJ06pYXAQ7ONXl98Bk3/1fUkJ6WpOKVw1P+4OVMJOf48DQWMw';
		$image_data .= 'XVLkTity+UPGdigmcf+WctT10gOGKf/A4JUyYfl7Qnx+A4hOabPVg9q9ZZ1OMwCoXEr0I6cBqBhSvXffyH2lLVQLNABKNBFffXmGsEdNsMe0nV2R8';
		$image_data .= 'MmeLcmIW/2VkXjNToqfbOz8QQ406Jc3p6QTkxGG0PR2mTzqGQD4/twdja8wxK6E2p29EchGV5EW7OZwftAYM4Z5vaQQ6H8nBYGqwwnoBPzP3suMI3';
		$image_data .= 'xRl8zXMTMEtHnn23QMERi/5cFMYlm4UpuCEVCvsXffL5OWFIPYJhIjFcpwkagYYZZoGGACPj8k/IgQT25+TYa2NBhtBu/TOiEvRL1YJyGhqCgUYE4';
		$image_data .= 'Tj2FDvjOCh1zg2uACfy4MEoCI10ztq2Ns89yIGnXoPwaKJQZkcejCzh0FGXoaNvKZdK4GiiJE6GUd58CqcIRvizhqOxwg3KBrKjH85l8tyYG0JZAN';
		$image_data .= '4FCkftyDT2XkQoULoojUumCGN+x3mcMzRERaSwvjDEpEYyF/LsE9VABRVmnEYtFnBkGJp5YD/7B4L58G+IQGpEkRFcd1kyx2QQURBMqA5oRP7aQEF';
		$image_data .= '1teAah0KvQ/OflhSIjmsQH6kUDicVubGiovJI9LHakYAD8UhPRVtwN0b3ZA1DYwqUjeq92AfW+WhQC8500W8YVskX8MJG/vECe25ZLB4cF5V/DeRY';
		$image_data .= 'b9IXoihDobUs3fcbZshYmGWoXsWWRhCFNDAKEkAtI8MfuTF//BVo1EVJB/EsSCOCRyPQOEgJLpAoPZ2Cf9Gj5tORJUUJQ7BIpXsSxl6bXJ6K45Cqi';
		$image_data .= 'NKkPxwNUybcOPZRGBrRctEL/5bFnR1QOG59OHJwlFbExsbGxF5cNOBplLuf0CRFBkRoskzxl2R6htLoKDTsh9ZjYII6TmBDRj9yC4eXGWbAMEXCoz';
		$image_data .= 'FiqfzpWo9+VATFWULDs9FZLgoxNz4hjULnpBM4IGXoJc3Sb5N1IBxtkaBIpXPIR4arGx9AUnA/W8zl4mGx2A2shpgZZp3vRqTPCFqVnIkmxioG73Z';
		$image_data .= 'KI9nExEQohJf6n0rnKD+CWGYoNETqH43K/BbdqH4kszW4d1rVvNRM1TCiDuWKf6TjTMUj1cdFRjU0Nv6aABbWOUqi+VFRwbOkg56k2RQ6Hf6n0xM9';
		$image_data .= 'LRUnWdSDJnNgCwW2cbY/mDe83/gNCqbAjQ8vzHyrPOzrRUwY5uMTY/zKf/MUcyXvbGxs5u/qQ2wa+62ehYZGTYfj4N8/5O3qGG1fs9NR+lBDW52QJ';
		$image_data .= 'JvYE0YZHCWRjgaN+nkIa3Rs1+b90B491BUlz2ifMweytQ7pBo3aNvDIH45Z7nyotW+n5bw7I7mv+0xiRpDHnIsPS1ihZ5nEhGm1y5aRaZhWnz0bWx';
		$image_data .= 'GPvbkaRAOMFpoRZF6RPVL6uJKPz5m68Ub+seSKzMyzVWcrvv0b0XgOJhySgler+FtAx2RUTQfY9UGq5J2tqnJ7kvc3onEKKkY185uFA4ymxS00cJs';
		$image_data .= '71BlyQ3GD5lHLrQJx9bC/DY2xoVvBiKLqOVComorP1uoNHtV/zdyw29qq1oWLnrqIk//ML4FYmlAlBw1/Bq/pYf83aExIvskV8HYPOanzOPSKWMy8';
		$image_data .= '+c3zwf5EzGcy+eL8/MzMTN7mP/PhE8c7YU4Og9ZOVXynuQTDbB79N2iY3kQ1Y7jpvsWovt4cWDsm5IuJJpSfmS/gD6osI36rbIe7mOZ0hRggIqZb8';
		$image_data .= 'eiALgyzs5Oeif3qaLCSUe9gLh5m162IBn/3wMHaTdkDS65Y9qzA9sIjrPPCxgAdUev0MSJf7JHIzqlrog76NBuRyBbrdLinc8EJs7H11XHS7BrzSO';
		$image_data .= 'Slgvm26rQCGl+dMY907DCbLk27qaLWXtEvDk6ijRicyaib7aCj0/uqaLAX8UfqHKbMoVgt/OwWk8/lcvlM3mfySZHultuYbVdvy+3pjy50e93Gpnv';
		$image_data .= '12onGT3yMYQ5dvktEd7t/0VH5bjxmc5dlM5V97182vjZdPaJ77EcQCKxHRtNnLIEz2N01YnU6TRT52hk98oUzfW/b2dkTcEHzVWWKI5Nfzdww3DzG';
		$image_data .= 'Ojcmj1k1uKjsvV+56Juvdlv9prjS1gHrWuJgC0s6t1s3YphRANaF3mnOgLvpnsGaAR/spWlzF3P6HuttgevoUvnF9m5nCyTL7VZMBMVCND1ghu10L';
		$image_data .= 'MAIwzrH3LWBL6t7vM3du+NVXhUNrPQbl7l7h933/bnJuzcNvWlQG5jupSLCWsfAktG91u/gznux1onwzm5GD3p5LLLD2EDDFtH4TgfDeo16Z/QGBP';
		$image_data .= 'iiKgo04N8Zvpivw4WAXqDh4NSJ6ihr/MaAgCWsV+c32COb7z9xJQ4tY7CJM7qxjS2aRmMwp3+pYEaIBmbUiWFTp2Je0zH2BTubTmzMVGyjCGjoPJ5';
		$image_data .= 'xG2OpEDS8JmK2/2vn0IvdbYWMwb5/1NvSjS1RMYIIUWG/Ohr/OfX+D6TEIy8dr+8AiMhhauu9li7UGuyMRKLWxzbjobHqfN/r5aQzw6kVGq1vyxgV';
		$image_data .= '2BmFwEQjzKlF1NnqcM+rS9SLdeo4XfDyHQMbN2p2iUTfs95EGk6tUrcArxD4mo8faxJfq283egesejTBf6H1jx8TTswX4cLGwJF2aB84Gt067KLZA';
		$image_data .= '8s9kmPfPBoqRhf+6pltW757fcaw/xlpTvzLX6JKN/Z3o/F3md94S+OtJPp/AQYA+87vvNe+Yy0AAAAASUVORK5CYII=';

		$output .= '<h2>'.htmlspecialchars($this->displayName).'</h2>
			<img style="float:left; margin-right:15px;" src="'.$image_data.'"/>'
			.$this->l('We help customers and retailers resolve shopping issues quickly and effectively.').'</b><br /><br />'
			.$this->l('Youstice is a global online application for customers and retailers').'<br />'
			.$this->l('It allows quick and efficient communication between shops and customers').'<br />'
			.$this->l('Complaints are resolved in just a few clicks.').'<br /><br /><br />';

		$output .= $this->addReportClaimsInfo();
		$output .= $this->displayForm();

		return $output;
	}

	protected function addReportClaimsInfo()
	{
		$report_claims_text = $this->l('Claims reporting for logged out users is available at');

		return '<p>'.$report_claims_text.' <input type="text" style="min-width:500px" onclick="select()"
				value="'.$this->getReportClaimsPageLink().'"></p>';
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$options_sandbox = array(
			array(
				'id_option' => '0',
				'name' => $this->l('No')
			),
			array(
				'id_option' => '1',
				'name' => $this->l('Yes')
			),
		);

		$options_item_types = array(
			array(
				'id_option' => 'product',
				'name' => $this->l('Products')
			),
			array(
				'id_option' => 'service',
				'name' => $this->l('Services')
			)
		);

		// Init Fields form array
		$fields_form = array();
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type'  => 'text',
					'label' => $this->l('Api Key'),
					'name'  => 'YRS_API_KEY',
					'size'  => 40,
					'required' => true
				),
				array(
					'type' => 'select',
					'label' => $this->l('Use sandbox environment'),
					'name' => 'YRS_SANDBOX',
					'required' => true,
					'options' => array(
					'query' => $options_sandbox,
					'id'	=> 'id_option',
					'name'	=> 'name'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('This e-shop sells'),
					'name' => 'YRS_ITEM_TYPE',
					'required' => true,
					'options' => array(
					'query' => $options_item_types,
					'id'	=> 'id_option',
					'name'	=> 'name'
					)
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		$helper = new HelperForm();

		// Module, token and currentIndex
		$helper->module          = $this;
		$helper->name_controller = $this->name;
		$helper->token           = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->default_form_language    = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->show_toolbar   = false;
		$helper->toolbar_scroll = true;
		$helper->submit_action  = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		// current values
		$helper->fields_value['YRS_API_KEY']		 = Configuration::get('YRS_API_KEY');
		$helper->fields_value['YRS_SANDBOX']		 = Configuration::get('YRS_SANDBOX');
		$helper->fields_value['YRS_ITEM_TYPE']		 = Configuration::get('YRS_ITEM_TYPE');

		return $helper->generateForm($fields_form);
	}

	protected function getReportClaimsPageLink()
	{
		$base = Tools::getShopDomainSsl(true).__PS_BASE_URI__;
		return $base.'index.php?fc=module&module=yousticeresolutionsystem&controller=yrs&action=getReportClaimsPage';
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		$db = array(
			'driver' => 'mysql',
			'host' => _DB_SERVER_,
			'user' => _DB_USER_,
			'pass' => _DB_PASSWD_,
			'name' => _DB_NAME_,
			'prefix' => _DB_PREFIX_
		);

		$y_api = YousticeApi::create();
		$y_api->setDbCredentials($db);
		$y_api->setLanguage($this->context->language->iso_code);
		$y_api->setShopSoftwareType('prestashop');
		$y_api->setThisShopSells('product');
		$y_api->setApiKey(Configuration::get('YRS_API_KEY'), Configuration::get('YRS_SANDBOX'));

		$this->y_api->install();

		return parent::install() &&
			$this->registerHook('header') &&
			$this->registerHook('footer') &&
			$this->registerHook('orderDetail') &&
			Configuration::updateValue('YRS_ITEM_TYPE', 'product') &&
			Configuration::updateValue('YRS_API_KEY', '');
	}

	public function uninstall()
	{
		$this->y_api->uninstall();

		if (!parent::uninstall() ||
				!Configuration::deleteByName('YRS_ITEM_TYPE') ||
				!Configuration::deleteByName('YRS_API_KEY'))
			return false;

		return true;
	}

}
