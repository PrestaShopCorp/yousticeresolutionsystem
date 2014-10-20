Index: SDK/Api.php
===================================================================
--- SDK/Api.php	(revision 120)
+++ SDK/Api.php	(working copy)
@@ -28,12 +28,6 @@
 
 	/**
 	 *
-	 * @var type YousticeTranslator
-	 */
-	protected $translator;
-
-	/**
-	 *
 	 * @var SessionProviderInterface 
 	 */
 	protected $session;
@@ -169,20 +163,6 @@
 		}, true, true);  //prepend our autoloader
 	}
 
-	/**
-	 * Renders form with fields email and orderNumber for reporting claims
-	 * @return string html
-	 */
-	public function getReportClaimsFormHtml()
-	{
-		if (!trim($this->api_key))
-			return "Invalid shop's api key";
-
-		$widget = new YousticeWidgetsReportClaimsForm($this->language);
-
-		return $widget->toString();
-	}
-
 	public function getShowButtonsWidgetHtml()
 	{
 		if (!trim($this->api_key))
@@ -190,7 +170,7 @@
 
 		$reports_count = count($this->local->getReportsByUser($this->user_id));
 
-		$widget = new YousticeWidgetsShowButtons($this->language, $reports_count > 0);
+		$widget = new YousticeWidgetsShowButtons($reports_count > 0);
 
 		return $widget->toString();
 	}
@@ -229,7 +209,7 @@
 				$href = $remote_link;
 		}
 
-		$web_button = new YousticeWidgetsWebReportButton($href, $this->language, $report);
+		$web_button = new YousticeWidgetsWebReportButton($href, $report);
 
 		return $web_button->toString();
 	}
@@ -257,7 +237,7 @@
 				$href = $remote_link;
 		}
 
-		$product_button = new YousticeWidgetsProductReportButton($href, $this->language, $report);
+		$product_button = new YousticeWidgetsProductReportButton($href, $report);
 
 		return $product_button->toString();
 	}
@@ -284,7 +264,7 @@
 				$href = $remote_link;
 		}
 
-		$order_button = new YousticeWidgetsOrderReportButton($href, $this->language, $report);
+		$order_button = new YousticeWidgetsOrderReportButton($href, $report);
 
 		return $order_button->toString();
 	}
@@ -307,7 +287,7 @@
 
 		$report = $this->local->getOrderReport($order->getId(), $product_codes);
 
-		$order_button = new YousticeWidgetsOrderDetailButton($href, $this->language, $order, $report, $this);
+		$order_button = new YousticeWidgetsOrderDetailButton($href, $order, $report, $this);
 
 		return $order_button->toString();
 	}
@@ -329,7 +309,7 @@
 
 		$report = $this->local->getOrderReport($order->getCode(), $product_codes);
 
-		$order_detail = new YousticeWidgetsOrderDetail($this->language, $order, $report, $this);
+		$order_detail = new YousticeWidgetsOrderDetail($order, $report, $this);
 
 		return $order_detail->toString();
 	}
@@ -463,17 +443,6 @@
 	}
 
 	/**
-	 * 
-	 * @param string $string to translate
-	 * @param array $variables
-	 * @return string translated
-	 */
-	public function t($string, $variables = array())
-	{
-		return $this->translator->t($string, $variables);
-	}
-
-	/**
 	 * Create necessary table
 	 * @return boolean success
 	 */
@@ -638,7 +607,6 @@
 		if ($lang && YousticeHelpersLanguageCodes::check($lang))
 		{
 			$this->language = $lang;
-			$this->translator = new YousticeTranslator($this->language);
 		}
 		else
 			throw new InvalidArgumentException('Language code "'.$lang.'" is not allowed.');
Index: SDK/Helpers/HelperFunctions.php
===================================================================
--- SDK/Helpers/HelperFunctions.php	(revision 120)
+++ SDK/Helpers/HelperFunctions.php	(working copy)
@@ -25,13 +25,16 @@
 		return htmlspecialchars($string, ENT_QUOTES);
 	}
 
-	public static function remainingTimeToString($time = 0, YousticeTranslator $translator)
+	public static function remainingTimeToDays($time = 0)
 	{
+		return floor($time / (60 * 60 * 24));
+	}
+
+	public static function remainingTimeToHours($time = 0)
+	{
 		$days = floor($time / (60 * 60 * 24));
 
-		$hours = floor(($time - ($days * 60 * 60 * 24)) / (60 * 60));
-
-		return $translator->t('%d days %d hours', $days, $hours);
+		return floor(($time - ($days * 60 * 60 * 24)) / (60 * 60));
 	}
 
 	public static function isSessionStarted()
Index: SDK/Translator.php
===================================================================
--- SDK/Translator.php	(revision 120)
+++ SDK/Translator.php	(working copy)
@@ -1,38 +0,0 @@
-<?php
-/**
- * Class handles local module translations
- *
- * @author    Youstice
- * @copyright (c) 2014, Youstice
- * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
- */
-
-class YousticeTranslator {
-
-	private $strings = array();
-
-	public function __construct($lang = 'en')
-	{
-		$file = dirname(__FILE__)."/languageStrings/{$lang}.php";
-
-		if (file_exists($file))
-			$this->strings = include $file;
-	}
-
-	public function setLanguageStrings($strings)
-	{
-		$this->strings = $strings;
-	}
-
-	public function t($string)
-	{
-		$variables = func_get_args();
-		array_shift($variables);
-
-		if (array_key_exists($string, $this->strings))
-			return vsprintf($this->strings[$string], $variables);
-
-		return vsprintf($string, $variables);
-	}
-
-}
Index: SDK/Widgets/OrderDetail.php
===================================================================
--- SDK/Widgets/OrderDetail.php	(revision 120)
+++ SDK/Widgets/OrderDetail.php	(working copy)
@@ -10,13 +10,11 @@
 class YousticeWidgetsOrderDetail {
 
 	protected $api;
-	protected $lang;
 	protected $report;
 	protected $order;
 
-	public function __construct($lang, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
+	public function __construct(YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
 	{
-		$this->translator = new YousticeTranslator($lang);
 		$this->order = $order;
 		$this->report = $report;
 		$this->api = $api;
@@ -29,7 +27,6 @@
 		$smarty = Context::getContext()->smarty;
 		$smarty->assign('orderName', $this->order->getName());
 		$smarty->assign('orderButton', $this->api->getOrderReportButtonHtml($this->order->getHref(), $this->order->getCode()));
-		$smarty->assign('productsMessage', 'Products in your order (%d)');
 		$smarty->assign('productsMessageCount', count($products));
 		$smarty->assign('products', $products);
 		$smarty->assign('api', $this->api);
Index: SDK/Widgets/OrderDetailButton.php
===================================================================
--- SDK/Widgets/OrderDetailButton.php	(revision 120)
+++ SDK/Widgets/OrderDetailButton.php	(working copy)
@@ -11,13 +11,11 @@
 
 	protected $api;
 	protected $href;
-	protected $translator;
 	protected $report;
 
-	public function __construct($href, $lang, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
+	public function __construct($href, YousticeShopOrder $order, YousticeReportsOrderReport $report, $api)
 	{
 		$this->href = $href;
-		$this->translator = new YousticeTranslator($lang);
 		$this->order = $order;
 		$this->report = $report;
 		$this->api = $api;
@@ -77,7 +75,6 @@
 
 		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
 		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
-		$smarty->assign('message', '%d ongoing cases');
 		$smarty->assign('messageCount', $count);
 		$smarty->assign('popup', $popup);
 
@@ -103,7 +100,9 @@
 		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
 		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
 		$smarty->assign('message', $status);
-		$smarty->assign('remainingTime', YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator));
+		$remainingTime = $this->report->getRemainingTime();
+		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
+		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
 		
 		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderDetailButton/reportedButtonWithStatus.tpl');
 	}
Index: SDK/Widgets/OrderReportButton.php
===================================================================
--- SDK/Widgets/OrderReportButton.php	(revision 120)
+++ SDK/Widgets/OrderReportButton.php	(working copy)
@@ -10,13 +10,11 @@
 class YousticeWidgetsOrderReportButton {
 
 	protected $href;
-	protected $translator;
 	protected $report;
 
-	public function __construct($href, $lang, YousticeReportsOrderReport $report)
+	public function __construct($href, YousticeReportsOrderReport $report)
 	{
 		$this->href = $href;
-		$this->translator = new YousticeTranslator($lang);
 		$this->report = $report;
 	}
 
@@ -51,7 +49,9 @@
 		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
 		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
 		$smarty->assign('message', $status);
-		$smarty->assign('remainingTime', YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator));
+		$remainingTime = $this->report->getRemainingTime();
+		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
+		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
 		
 		return $smarty->fetch(YRS_TEMPLATE_PATH.'orderButton/reportedButtonWithStatus.tpl');
 	}
Index: SDK/Widgets/ProductReportButton.php
===================================================================
--- SDK/Widgets/ProductReportButton.php	(revision 120)
+++ SDK/Widgets/ProductReportButton.php	(working copy)
@@ -10,13 +10,11 @@
 class YousticeWidgetsProductReportButton {
 
 	protected $href;
-	protected $translator;
 	protected $report;
 
-	public function __construct($href, $lang, YousticeReportsProductReport $report)
+	public function __construct($href, YousticeReportsProductReport $report)
 	{
 		$this->href = $href;
-		$this->translator = new YousticeTranslator($lang);
 		$this->report = $report;
 	}
 
@@ -51,7 +49,9 @@
 		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
 		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
 		$smarty->assign('message', $status);
-		$smarty->assign('remainingTime', YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator));
+		$remainingTime = $this->report->getRemainingTime();
+		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
+		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
 		
 		return $smarty->fetch(YRS_TEMPLATE_PATH.'productButton/reportedButtonWithStatus.tpl');
 	}
Index: SDK/Widgets/ReportClaimsForm.php
===================================================================
--- SDK/Widgets/ReportClaimsForm.php	(revision 120)
+++ SDK/Widgets/ReportClaimsForm.php	(working copy)
@@ -1,125 +0,0 @@
-<?php
-/**
- * Youstice form for reporting claims.
- *
- * @author    Youstice
- * @copyright (c) 2014, Youstice
- * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
- */
-
-class YousticeWidgetsReportClaimsForm {
-
-	protected $action;
-	protected $translator;
-
-	public function __construct($lang)
-	{
-		$this->translator = new YousticeTranslator($lang);
-	}
-
-	public function toString()
-	{
-		$order_number_text = $this->translator->t('Order number');
-		$description_text = 'Would you like to file a complaint and report on your shopping issue? Simply enter the details below:';
-
-		$output = '<h2>'.$this->translator->t('File a complaint').'</h2>';
-		$output .= '<img style="float:left; margin-right:15px;" src="'.$this->getImageData().'"/>';
-		$output .= '<p style="clear:left;max-width:300px;padding-top:8px">'.$this->translator->t($description_text).'</p>';
-		$output .= '<form action="" method="post" id="yReportClaims">';
-		$output .= '<label for="yEmail">Email</label>';
-		$output .= '<input type="email" id="yEmail" name="email">';
-		$output .= '<label for="yOrderNumber">'.$order_number_text.'</label>';
-		$output .= '<input type="text" id="yOrderNumber" name="orderNumber">';
-		$output .= '<input type="submit" name="send" value="'.$this->translator->t('Continue').'">';
-		$output .= '</form>';
-
-		return $output;
-	}
-
-	protected function getImageData()
-	{
-		$image_data  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccl';
-		$image_data .= 'lPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+';
-		$image_data .= 'IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxM';
-		$image_data .= 'i8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YX';
-		$image_data .= 'gtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHh';
-		$image_data .= 'tbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9i';
-		$image_data .= 'ZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc';
-		$image_data .= '3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIE';
-		$image_data .= 'NTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTU';
-		$image_data .= 'wMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJE';
-		$image_data .= 'Rj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2rXaqxaLH27faTk5S0crO9j';
-		$image_data .= 'LzFkMS9hLx9EnvSrNGampuwZ7B2DHSta6yeXJ3ExMWGIYScVJuLHIircaquYa3MnMu0g7OzfLKzs7Ty6PK7u7zDjMKvY656enzp2emKGYf19fWpYa';
-		$image_data .= 'iqqqulXaXNqszJycqMIol8JHqvaq5uDmzo1ujx5vHU1NXQp9ClUKOKiotlAWSdQJrEmsOjo6SnYaepZKj06vTt7e3y8vKDg4Tl0eWfV578+/yJIob';
-		$image_data .= '8+fzd3d7+/v6EHoLiyOGUMZLAib+NJIl+Gny9kbz69vqSS5CCMYHhxeCSKo+TLpDewd3Vr9SDFYCjTaHev92OK4zVutWLPYqoXqekaqPp6emdTJt4';
-		$image_data .= 'FnaROI/t3e3m1eWxd7D27/baudp2FXSkX6KhXaDgyN+sZKrZwdmNMouZPpeSJ4/exd6ya7CgRp51GnPZ2dnfyd7SsdKAKX/48/j48viOIIt5GHmNQ';
-		$image_data .= 'otpBmiQIY3s2uysYKuaOpiQJY3FoMW5fLjYutji0OKCGYBqCGlxEXCmWqWVUJPo0+jhzOC2bLS3ebXWtNWjWKHHk8b+/P6nVKb59vloaGq0aLLZtd';
-		$image_data .= 'hwEm6OJItoBGaBHICRJo5uD22KIYfTtdKYNZXIpMdxDHB4EHblzeSzb7KPj5CJFobq3OluCW2ZVZdyFHGTOpFsCmvkyuOSQJDs2OtiYmTz6fOoaae';
-		$image_data .= 'iZKG5driGF4N/Hn7ZvtiIHoXXsteHGYSRJI7Il8fcwNzdvdx0FXOPRo59F3uILYakW6JtDGyGhod+foD69Pr59PmJO4eenqB0EHLbw9vOr82pV6ia';
-		$image_data .= 'Qpfv4++GN4R4H3e5g7jUt9PIm8iQJo21arOwYK5gAF9rDWqurq/X19f38PeXl5jb29zv7/C3t7inp6j9/f3Pz8/69vm4ibe2dLXg4ODn0ueXSpazb';
-		$image_data .= 'LN6GXqIJ4br2+u/v8BgYGJxcXP6+vqINYeCEH/59vrcxdv07PT7+Pt8HXvz6/Lk0uP///9A738jAAAT0ElEQVR42uyaCVgT57rHWRN2BEQIYRGjAc';
-		$image_data .= 'KOQlQELUGCsihKhaKouAKHKuJGDQYRtQRcEJGrEAgFoqHAwXoFQQqotUbaYkUqdUUpFqu0Qj3V3mrt3Pebycp2bJ+jR/v4b4mT2TLzy7v8v2+igr2';
-		$image_data .= 'VXCpvEbyl8VdoPP32kvtbGhJNqDE393N8SwNXrrV5XFy838dvaeB5ohEfHxdnvv8tDaQva/zi4uPNv34zbuTZrHmgd9tfWt0oNY+P97P44M2goW2c';
-		$image_data .= 'RqVS2xa8NBq5O/z8LDzekCDXPqoFon768jrsR+Z+Fs9ex1tnbTuO9KUSDeOXS2OmhV+8+anXkcadh1QajUb+8VXGxiZUN6zZryMNXTwQlGgYk19u3';
-		$image_data .= 'bhvbgo4JryeNIwH0Hh2ZjXo8PKXRaPd1NQ03tR8ypsRGy/bb5RWIhqmGqteQxpHXzWNvBo/nIb5R68fjS+OkhAN91dH430IDcTDbyVrhOO/HHtCpr';
-		$image_data .= 'F4N9aWrxkra4HaRxZc1ddvnrROZl/YPid8CJ2Q+LtcH6lOzFHsbMdXqzUfaFZbPYFYy4Ljlh81BtHm4XuzsB75kV8oXd2JWaMsDzRbLpjlMbAXjJ2';
-		$image_data .= 'HtqitXt7zYjSsgEZNqDkExx8j0NicM817mkRx69CaDu+EGvQuoSaBMYrY6Q/90YysLAYDXhIMTs8kQu8ByZWQ/STJBSYZa7m6JiW5aiW8Kzv/lgXZ';
-		$image_data .= 'NDI5jZpGJlNPTvJBI2vjpKQkLURDK+khSGsbpv0AXwLRDssvjX1s3ENoMzTqjRs3Dhkc3qJw1cfGXb9BiGYw70VoTNhtGl+54zlU0srQEWiMMo+Hu';
-		$image_data .= '2TExcUx4vxwGtZZDO9oXN45OI12vegse4QHaVp0VvYRnMZR+4Q0fFWWGnEqDxI1gQb/kUhZ70lPv+cBmWYsFY18dBaGuaeRSCQoG2gNCf6oxzHth9';
-		$image_data .= 'KdyHK/sTyIStMidOjQIdqNk8dkcTEO8Bw6dP369X37rt9o2+nx72lYiK8kP3HHrMBz1D4dgYYfgIjTYMRpaGjk4DR2MrylwmmsS8BZSHEkJEQzJkH';
-		$image_data .= 'c5unaJ5AS4NZJ0TIaUpGlseFOSiMZK4hG3YO5UxXXaGnRjkt8KFKajMa7WlQtuXAekihw170BGPZdP0Rr81y/ffZ8yuR2JRqnTHeH5g0Yzl8RC7hb';
-		$image_data .= 'e7BvzyY/ebJjIIN/TJnyIbHkuNTvPOLB0DifY47fxEq/LIIFIyvSEcN2MRj2OA17CBYJj6zmHizPFaIHvZHTSCOnpRE0JLExJzsNjwBIEzKBJS0Ic';
-		$image_data .= '0cuCwdhTIIlKhloaFElcSCjsZoqAWRMoxkTPGj7fPCY2Uc7dKMtcnJ4E4dOT20qSkykRH6hQMORy+TfrFQaHm8J5ednckthKaw6syLMI0+p2Diagx';
-		$image_data .= 'bii6esrVaex6NjZYf1O2jNFKuOGgQj2qLD+hS2l+Ftj8RguFrv1PXOmiZNjmehHXoGrig8pDSeBhkEGSjRmEdGSUEijVtwZn9zEsoG2oPcdoOgIAM';
-		$image_data .= 'iMDqCkNSxLfrwz0NFGkcIGMZU45MGBieNqVoPH+7bd+MMbPE5WFISnlhET9RSO6xGS21KBJUHyWnMCeMLBG78HR++v7k01MpiceXcr8JcqgSZ4mRU';
-		$image_data .= 'ePT4VVWZLmFffVZZa2Glt+PSrn+s2GRuGqcRXyMtS2M1UNlQmDJ8WhOn4e19HrmBp6NxGt5ZQZtW9bDzJlydhsdDAgOvHaxse4gNad1A8jFOUKBhS';
-		$image_data .= 'cbhzCLqgC4N0cBjeDkeKJA2cs15gNJGQkNbF4VKEk1LbVteLnvO8klpbW2R68P1YdPJ8qb0okSKJ9GdP52PcDSFt8totFeJBYBDfIvHZyLx+VxetZ';
-		$image_data .= 'sgk2mN18ANAsAhKBNXVyCJy6piU/pcksOWLVohvZDQHA2NuPPyRrzLD4rI+excWLyaA1FiH+29S7rNXRfHEZ2NujHrgX2CPDbwluiqSGMcSpw0A0z';
-		$image_data .= 'OhkrQ2EbQUHRfXzwwlseGGhVvMCdlPl2N09TUhELAPT09MbGIEym1lJMoKDjoB2Q08pK5iIab2E0glZtYLOYJiBFKLVOQmVlVVXUWFEvoIvqLUZd+';
-		$image_data .= '1N6fzHPiNc7LvFDHeQ1vDb/NqBRIKoi8Y2Ieo+1RtmSdRlZiZTRkCkOBxlicRrSEhj6iQd0p8QpqCUlJxtn/NwyNDx5oyTrsCWrbwcj12yeryz91f';
-		$image_data .= 'mp6Oh1oGNDh5ouajsjmcNanwgqOJ1tWN3ageODxCBAoPvjcW/kbNiyW3KuLy4Yqt2oel8fNJICUJZeVlfGt5a51WWy9WbKZdEW7hgaqmicIN2IPre';
-		$image_data .= 'WqYtGZxUDtJdoApzEwNiQ0JPQm4ZmSJnEtW8B2PX+KX/Y22qBR2wf7yOSDnpPxc12lpxekppZbKoy5ZkO1pI/DZk5GN0+hybcEITxNsz3kPeXS7q3';
-		$image_data .= '3P0pmAgtx/uL7oTsuvb/3Y49VUvv2w3P1FZsWOk7Rs/oKcMRmVqz0uL9oa6iCuwut6EtJESZLok8v5PM1y9YgNmxrc0a0t/c0H6WG1IFyxd7VR0qD';
-		$image_data .= 'MSyNn3FrQUob92Ouck/bhvwGjfyO4oh+e0F6U0H5OLRMphQVFaVu/1Jxegh03AcSpQjRsHw2Ryo1lCpFqccG+I32uUyoH7zK4QdppVcyq85WbCgdP';
-		$image_data .= 'Nqtj+nr6zMhvkPWJ8Jz586ZoETx+bzerL/frEZ57/1+0ajJQLA+zp5GciVFT1Kookm4O5Vm1tU21GBJZHK22hFFt/5jScns2eGc9xRplHDS04voqF';
-		$image_data .= 'Bu2Z4KNCi6Q9zCp3iZSNw+WabtCE8i5fBA98Wy4FYLBEyXYWa75izmQfnghm0aYlutSUpK38V4wovEnAsJMXyCLt49BMnkvvLOswL7+/ujAiEi5ix';
-		$image_data .= 'ds2xZyZogBa6X14JmS+dsviQ5w12Hh0cVFBevNZCHwjGKs3NBAeXMQBrpOI3l85uKihLpzUNc6FU6TqMpVSa8xSZSFgz2oqX50Gu5t4Z04h9v5WdW';
-		$image_data .= 'ZfIrnw+18fTFFFA93qdWmiACHTiYcyGFhYVCtQFhHlhYGBhoqIcKjmpgYKFhtnybewPSr7Ij7jxojGgoLkb33lDnTJK21GPFBQXD09hTkA6xQR81x';
-		$image_data .= 'IXqD6JBqPzTIZz53jAoHtXMxXmDTrPQBWCIq61yh561/QThKEMTQ3lrYvpC+kLcJTRCCusH00AiaGSYmZkZ7pRvezejuNjfX1XhiHnRxRENzs7BcP';
-		$image_data .= 'fOdcGWL0SjCacxaXgaay9PVtb2Y0ONU55XQvFwY24d+LgxVMzLz+dtGPah7JQKoHHxJ4RNGBLSJzTH165AiVJoojfA1qviNK7iNAKVaRxQ9S8uLo5';
-		$image_data .= 'Q4vejwdrGugacR3HdVTmN4GEzJbEJ0dAf4jotcRoF8/IGij30qM2KC62WeeVbxQmB57VMcb6A+c3wQ3uP+tiUlJgUiIiasj5IFALb034IjhBhjvK+';
-		$image_data .= 'zUKcxi6CBiwp0MiBYBlIA+rLad3ZEcXBwKPYeQ9BwxnFxntD07hTgldR2hDXuRqvopRJLzzbc2kD4OBfUWwtVjfBmHFNR5oSrKkAGGX3Me3k2L6+m';
-		$image_data .= 'M/vEDMMPwlRdASqK+7JXmoICAr91WU0Vsq2vdNghjSQBmo2zeF4eNThRWYWHhv01UPTwNoo0F6awscOvszl89MRDeqLz339sUEs4H+mGBsfQXHlW4';
-		$image_data .= 'w4h7buYkxMysVF2KUy6C5l0vvTQxU1RKh06GYEI1B1KfrO/4loqDJk26JVcRqSumGZcDTJ1UB6Ie7hUEyDKetR5TqC143G5mFo6Jeno2WDwZfZ48l';
-		$image_data .= 'B9qLg2AvT8AAaTCulabMr1QKm9Yg02J8AjhjhilqgkRK7VzplFIJSJcRQod78/Pk56CiBeGllX4YS0lC4RmqTmgkYUho0k4yIX9fKjsyuQzT+iXZW';
-		$image_data .= 'v4bipGF9rhKNggIJjW3XCoBGk7Ni7GB7krRlLZazXlthyw9nRqDhyBS4cd9XWjWXL+B+1TMijikVQKOs40ksqqayXUeXIRrnzsm8+bo1htB0C1WX4';
-		$image_data .= 'QTOE3FCGI4e/cJAMzxVVIm8Torw92+YLcvPo0DDuWEtGu7NvNwQDGWkcWeuMo2CcqJ06pYXAQ7ONXl98Bk3/1fUkJ6WpOKVw1P+4OVMJOf48DQWMw';
-		$image_data .= 'XVLkTity+UPGdigmcf+WctT10gOGKf/A4JUyYfl7Qnx+A4hOabPVg9q9ZZ1OMwCoXEr0I6cBqBhSvXffyH2lLVQLNABKNBFffXmGsEdNsMe0nV2R8';
-		$image_data .= 'MmeLcmIW/2VkXjNToqfbOz8QQ406Jc3p6QTkxGG0PR2mTzqGQD4/twdja8wxK6E2p29EchGV5EW7OZwftAYM4Z5vaQQ6H8nBYGqwwnoBPzP3suMI3';
-		$image_data .= 'xRl8zXMTMEtHnn23QMERi/5cFMYlm4UpuCEVCvsXffL5OWFIPYJhIjFcpwkagYYZZoGGACPj8k/IgQT25+TYa2NBhtBu/TOiEvRL1YJyGhqCgUYE4';
-		$image_data .= 'Tj2FDvjOCh1zg2uACfy4MEoCI10ztq2Ns89yIGnXoPwaKJQZkcejCzh0FGXoaNvKZdK4GiiJE6GUd58CqcIRvizhqOxwg3KBrKjH85l8tyYG0JZAN';
-		$image_data .= '4FCkftyDT2XkQoULoojUumCGN+x3mcMzRERaSwvjDEpEYyF/LsE9VABRVmnEYtFnBkGJp5YD/7B4L58G+IQGpEkRFcd1kyx2QQURBMqA5oRP7aQEF';
-		$image_data .= '1teAah0KvQ/OflhSIjmsQH6kUDicVubGiovJI9LHakYAD8UhPRVtwN0b3ZA1DYwqUjeq92AfW+WhQC8500W8YVskX8MJG/vECe25ZLB4cF5V/DeRY';
-		$image_data .= 'b9IXoihDobUs3fcbZshYmGWoXsWWRhCFNDAKEkAtI8MfuTF//BVo1EVJB/EsSCOCRyPQOEgJLpAoPZ2Cf9Gj5tORJUUJQ7BIpXsSxl6bXJ6K45Cqi';
-		$image_data .= 'NKkPxwNUybcOPZRGBrRctEL/5bFnR1QOG59OHJwlFbExsbGxF5cNOBplLuf0CRFBkRoskzxl2R6htLoKDTsh9ZjYII6TmBDRj9yC4eXGWbAMEXCoz';
-		$image_data .= 'FiqfzpWo9+VATFWULDs9FZLgoxNz4hjULnpBM4IGXoJc3Sb5N1IBxtkaBIpXPIR4arGx9AUnA/W8zl4mGx2A2shpgZZp3vRqTPCFqVnIkmxioG73Z';
-		$image_data .= 'KI9nExEQohJf6n0rnKD+CWGYoNETqH43K/BbdqH4kszW4d1rVvNRM1TCiDuWKf6TjTMUj1cdFRjU0Nv6aABbWOUqi+VFRwbOkg56k2RQ6Hf6n0xM9';
-		$image_data .= 'LRUnWdSDJnNgCwW2cbY/mDe83/gNCqbAjQ8vzHyrPOzrRUwY5uMTY/zKf/MUcyXvbGxs5u/qQ2wa+62ehYZGTYfj4N8/5O3qGG1fs9NR+lBDW52QJ';
-		$image_data .= 'JvYE0YZHCWRjgaN+nkIa3Rs1+b90B491BUlz2ifMweytQ7pBo3aNvDIH45Z7nyotW+n5bw7I7mv+0xiRpDHnIsPS1ihZ5nEhGm1y5aRaZhWnz0bWx';
-		$image_data .= 'GPvbkaRAOMFpoRZF6RPVL6uJKPz5m68Ub+seSKzMyzVWcrvv0b0XgOJhySgler+FtAx2RUTQfY9UGq5J2tqnJ7kvc3onEKKkY185uFA4ymxS00cJs';
-		$image_data .= '71BlyQ3GD5lHLrQJx9bC/DY2xoVvBiKLqOVComorP1uoNHtV/zdyw29qq1oWLnrqIk//ML4FYmlAlBw1/Bq/pYf83aExIvskV8HYPOanzOPSKWMy8';
-		$image_data .= '+c3zwf5EzGcy+eL8/MzMTN7mP/PhE8c7YU4Og9ZOVXynuQTDbB79N2iY3kQ1Y7jpvsWovt4cWDsm5IuJJpSfmS/gD6osI36rbIe7mOZ0hRggIqZb8';
-		$image_data .= 'eiALgyzs5Oeif3qaLCSUe9gLh5m162IBn/3wMHaTdkDS65Y9qzA9sIjrPPCxgAdUev0MSJf7JHIzqlrog76NBuRyBbrdLinc8EJs7H11XHS7BrzSO';
-		$image_data .= 'Slgvm26rQCGl+dMY907DCbLk27qaLWXtEvDk6ijRicyaib7aCj0/uqaLAX8UfqHKbMoVgt/OwWk8/lcvlM3mfySZHultuYbVdvy+3pjy50e93Gpnv';
-		$image_data .= '12onGT3yMYQ5dvktEd7t/0VH5bjxmc5dlM5V97182vjZdPaJ77EcQCKxHRtNnLIEz2N01YnU6TRT52hk98oUzfW/b2dkTcEHzVWWKI5Nfzdww3DzG';
-		$image_data .= 'Ojcmj1k1uKjsvV+56Juvdlv9prjS1gHrWuJgC0s6t1s3YphRANaF3mnOgLvpnsGaAR/spWlzF3P6HuttgevoUvnF9m5nCyTL7VZMBMVCND1ghu10L';
-		$image_data .= 'MAIwzrH3LWBL6t7vM3du+NVXhUNrPQbl7l7h933/bnJuzcNvWlQG5jupSLCWsfAktG91u/gznux1onwzm5GD3p5LLLD2EDDFtH4TgfDeo16Z/QGBP';
-		$image_data .= 'iiKgo04N8Zvpivw4WAXqDh4NSJ6ihr/MaAgCWsV+c32COb7z9xJQ4tY7CJM7qxjS2aRmMwp3+pYEaIBmbUiWFTp2Je0zH2BTubTmzMVGyjCGjoPJ5';
-		$image_data .= 'xG2OpEDS8JmK2/2vn0IvdbYWMwb5/1NvSjS1RMYIIUWG/Ohr/OfX+D6TEIy8dr+8AiMhhauu9li7UGuyMRKLWxzbjobHqfN/r5aQzw6kVGq1vyxgV';
-		$image_data .= '2BmFwEQjzKlF1NnqcM+rS9SLdeo4XfDyHQMbN2p2iUTfs95EGk6tUrcArxD4mo8faxJfq283egesejTBf6H1jx8TTswX4cLGwJF2aB84Gt067KLZA';
-		$image_data .= '8s9kmPfPBoqRhf+6pltW757fcaw/xlpTvzLX6JKN/Z3o/F3md94S+OtJPp/AQYA+87vvNe+Yy0AAAAASUVORK5CYII=';
-
-		return $image_data;
-	}
-
-}
Index: SDK/Widgets/ShowButtons.php
===================================================================
--- SDK/Widgets/ShowButtons.php	(revision 120)
+++ SDK/Widgets/ShowButtons.php	(working copy)
@@ -13,10 +13,9 @@
 	protected $has_reports;
 	protected $translator;
 
-	public function __construct($lang, $has_reports)
+	public function __construct($has_reports)
 	{
 		$this->has_reports = $has_reports;
-		$this->translator = new YousticeTranslator($lang);
 	}
 
 	public function toString()
Index: SDK/Widgets/WebReportButton.php
===================================================================
--- SDK/Widgets/WebReportButton.php	(revision 120)
+++ SDK/Widgets/WebReportButton.php	(working copy)
@@ -10,13 +10,11 @@
 class YousticeWidgetsWebReportButton {
 
 	protected $href;
-	protected $translator;
 	protected $report;
 
-	public function __construct($href, $lang, YousticeReportsWebReport $report)
+	public function __construct($href, YousticeReportsWebReport $report)
 	{
 		$this->href = $href;
-		$this->translator = new YousticeTranslator($lang);
 		$this->report = $report;
 	}
 
@@ -54,7 +52,9 @@
 		$smarty->assign('href', YousticeHelpersHelperFunctions::sh($this->href));
 		$smarty->assign('statusClass', 'yrsButton-'.YousticeHelpersHelperFunctions::webalize($this->report->getStatus()));
 		$smarty->assign('message', $status);
-		$smarty->assign('remainingTime', YousticeHelpersHelperFunctions::remainingTimeToString($this->report->getRemainingTime(), $this->translator));
+		$remainingTime = $this->report->getRemainingTime();
+		$smarty->assign('remainingTimeDays', YousticeHelpersHelperFunctions::remainingTimeToDays($remainingTime));
+		$smarty->assign('remainingTimeHours', YousticeHelpersHelperFunctions::remainingTimeToHours($remainingTime));
 		
 		return $smarty->fetch(YRS_TEMPLATE_PATH.'webButton/reportedButtonWithStatus.tpl');
 	}
Index: SDK/languageStrings/en.php
===================================================================
--- SDK/languageStrings/en.php	(revision 120)
+++ SDK/languageStrings/en.php	(working copy)
@@ -1,55 +0,0 @@
-<?php
-/**
- * Local English translations
- *
- * @author    Youstice
- * @copyright (c) 2014, Youstice
- * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
- */
-
-return array(
-	"Would you like to file a complaint?"		=> "Would you like to file a complaint?",
-	"Order number"								=> "Order number",
-	"File a complaint"							=> "File a complaint",
-	"Would you like to file a complaint and report on your shopping issue? Simply enter the details below:" => "Would you like to file a complaint and report on your shopping issue? Simply enter the details below:",
-	"In case you want to complain about a product or service, please follow this link."	=> "In case you want to complain about a product or service, please follow this link.",
-	"Continue"									=> "Continue",
-	//orderDetail
-	"Products in your order (%d)"				=> "Products in your order (%d)",
-	//buttons
-	"Report a problem"                          => "Report a problem",
-	"Report a problem unrelated to your orders" => "Report a problem unrelated to your orders",
-	"Problem reported"							=> "Problem reported",
-	"%d days %d hours"							=> "%d days %d hours",
-	"%d ongoing cases"                          => "%d ongoing cases",
-	//button's statuses
-	"To be implemented"							=> "To be implemented",
-	"Respond to retailer"						=> "Respond to retailer",
-	"Waiting for decision"						=> "Waiting for decision",
-	"Escalated to ODR"							=> "Escalated to ODR",
-	"Waiting for retailer's response"			=> "Waiting for retailer's response",
-    
-	//admin
-	"Youstice Resolution Module" => "Youstice Resolution Module",
-	"Your online justice"	=> "Your online justice",
-	"We help customers and retailers resolve shopping issues quickly and effectively." => "We help customers and retailers resolve shopping issues quickly and effectively.",
-	"Youstice is a global online application for customers and retailers"	=> "Youstice is a global online application for customers and retailers",
-	"It allows quick and efficient communication between shops and customers", "It allows quick and efficient communication between shops and customers",
-	"Complaints are resolved in just a few clicks."	=> "Complaints are resolved in just a few clicks.",
-	"Claims reporting for logged out users is available at" => "Claims reporting for logged out users is available at",
-	"Yes"	    => "Yes",
-	"No"	    => "No",
-	"Products"	    => "Products",
-	"Services"	    => "Services",
-	"Settings"	    => "Settings",
-	"Api Key"	    => "Api Key",
-	"Use sandbox environment"   => "Use sandbox environment",
-	"This e-shop sells"	    => "This e-shop sells",
-	"Save"			    => "Save",
-	"Are you sure you want to uninstall?"	=> "Are you sure you want to uninstall?",
-	"Settings were saved successfully."	=> "Settings were saved successfully.",
-	"Invalid API KEY"		=> "Invalid API KEY",
-	"Invalid Configuration value"	=> "Invalid Configuration value",
-	"Youstice - show logo"		=> "Youstice - show logo",
-	"No logo widget anchor found! Please add following code to file"	=> "No logo widget anchor found! Please add following code to file "
-);
Index: SDK/languageStrings/es.php
===================================================================
--- SDK/languageStrings/es.php	(revision 120)
+++ SDK/languageStrings/es.php	(working copy)
@@ -1,55 +0,0 @@
-<?php
-/**
- * Local Spain translations
- *
- * @author    Youstice
- * @copyright (c) 2014, Youstice
- * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
- */
-
-return array(
-	"Would you like to file a complaint?"		=> "¿Te gustaría presentar una reclamación?",
-	"Order number"								=> "Número de pedido",
-	"File a complaint"							=> "Presentar una reclamación",
-	"Would you like to file a complaint and report on your shopping issue? Simply enter the details below:" => "¿Te gustaría presentar una reclamación e informe sobre tu compra? No tienes más que ingresar tus datos a continuación:",
-	"In case you want to complain about a product or service, please follow this link."	=> "Si quieres presentar una queja sobre un producto o servicio, por favor, sigue este enlace.",
-	"Continue"									=> "Continuar",
-	//orderDetail
-	"Products in your order (%d)"				=> "Productos en tu pedido (%d)",
-	//buttons
-	"Report a problem"                          => "Notifica un problema",
-	"Report a problem unrelated to your orders" => "Notifica un problema que no esté relacionado con tus pedidos",
-	"Problem reported"							=> "Problema notificado",
-	"%d days %d hours"							=> "%d días  %d horas",
-	"%d ongoing cases"                          => "%d expedientes en trámite",
-	//button's statuses
-	"To be implemented"							=> "A ser ejecutados",
-	"Respond to retailer"						=> "Responder al comerciante",
-	"Waiting for decision"						=> "A la espera de una decisión",
-	"Escalated to ODR"							=> "Elevado a RDL",
-	"Waiting for retailer's response"			=> "A la espera de la decisión del comerciante",
-    
-	//admin
-	"Youstice Resolution Module" => "Módulo de Resolución de Youstice",
-	"Your online justice"	=> "Tu justicia en línea",
-	"We help customers and retailers resolve shopping issues quickly and effectively." => "Facilitamos la resolución rápida y efectiva de conflictos entre clientes y comerciantes.",
-	"Youstice is a global online application for customers and retailers"	=> "Youstice es una plataforma global en línea para clientes y comerciantes",
-	"It allows quick and efficient communication between shops and customers", "Facilita la comunicación rápida y eficaz entre clientes y comerciantes",
-	"Complaints are resolved in just a few clicks."	=> "Las reclamaciones se resuelven con tan solo unos clics.",
-	"Claims reporting for logged out users is available at" => "Presentación de reclamaciones para usuarios fuera del sistema disponible en",
-	"Yes"	    => "Sí",
-	"No"	    => "No",
-	"Products"	    => "Productos",
-	"Services"	    => "Servicios",
-	"Settings"	    => "Configuraciones",
-	"Api Key"	    => "Llave Api",
-	"Use sandbox environment"   => "Utiliza el entorno de prueba sandbox",
-	"This e-shop sells"	    => "Este comercio electrónico vende",
-	"Save"			    => "Guardar",
-	"Are you sure you want to uninstall?"	=> "¿Estás seguro de que quieres desinstalar?",
-	"Settings were saved successfully."	=> "Las configuraciones se han guardado con éxito.",
-	"Invalid API KEY"		=> "LLAVE API inválida",
-	"Invalid Configuration value"	=> "Valor de configuración inválido",
-	"Youstice - show logo"		=> "Youstice - mostrar logotipo",
-	"No logo widget anchor found! Please add following code to file"	=> "No logo widget anchor found! Please add following code to file "
-);
Index: SDK/languageStrings/index.php
===================================================================
--- SDK/languageStrings/index.php	(revision 120)
+++ SDK/languageStrings/index.php	(working copy)
@@ -1,35 +0,0 @@
-<?php
-/**
-* 2007-2014 PrestaShop
-*
-* NOTICE OF LICENSE
-*
-* This source file is subject to the Academic Free License (AFL 3.0)
-* that is bundled with this package in the file LICENSE.txt.
-* It is also available through the world-wide-web at this URL:
-* http://opensource.org/licenses/afl-3.0.php
-* If you did not receive a copy of the license and are unable to
-* obtain it through the world-wide-web, please send an email
-* to license@prestashop.com so we can send you a copy immediately.
-*
-* DISCLAIMER
-*
-* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
-* versions in the future. If you wish to customize PrestaShop for your
-* needs please refer to http://www.prestashop.com for more information.
-*
-*  @author    PrestaShop SA <contact@prestashop.com>
-*  @copyright 2007-2014 PrestaShop SA
-*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
-*  International Registered Trademark & Property of PrestaShop SA
-*/
-
-header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
-header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
-
-header('Cache-Control: no-store, no-cache, must-revalidate');
-header('Cache-Control: post-check=0, pre-check=0', false);
-header('Pragma: no-cache');
-
-header('Location: ../');
-exit;
Index: SDK/languageStrings/sk.php
===================================================================
--- SDK/languageStrings/sk.php	(revision 120)
+++ SDK/languageStrings/sk.php	(working copy)
@@ -1,55 +0,0 @@
-<?php
-/**
- * Local Slovak translations
- *
- * @author    Youstice
- * @copyright (c) 2014, Youstice
- * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
- */
-
-return array(
-	"Would you like to file a complaint?"		=> "Chceli by ste nahlásiť sťažnosť?",
-	"Order number"								=> "Číslo objednávky",
-	"File a complaint"							=> "Nahlásiť sťažnosť",
-	"Would you like to file a complaint and report on your shopping issue? Simply enter the details below:" => "Chcete podať sťažnosť a nahlásiť váš problém s nakupovaním? Jednoducho vyplňte údaje nižšie:",
-	"In case you want to complain about a product or service, please follow this link."	=> "Ak si želáte nahlásiť produkt alebo službu, prosím pokračujte týmto odkazom.",
-	"Continue"									=> "Pokračovať",
-	//orderDetail
-	"Products in your order (%d)"				=> "Produkty objednávky (%d)",
-	//buttons
-	"Report a problem"                          => "Nahlásiť problém",
-	"Report a problem unrelated to your orders" => "Nahlásiť problém netýkajúci sa objednávok",
-	"Problem reported"							=> "Problém bol nahlásený",
-	"%d days %d hours"							=> "%d dní %d hodín",
-	"%d ongoing cases"                          => "Nahlásenia: %d",
-	//button's statuses
-	"To be implemented"							=> "Implementuje sa",
-	"Respond to retailer"						=> "Zaslaná odpoveď obchodníkovi",
-	"Waiting for decision"						=> "Čakanie na rozhodnutie",
-	"Escalated to ODR"							=> "Poslané na ODR",
-	"Waiting for retailer's response"			=> "Čakanie na odpoveď od obchodníka",
-    
-	//admin
-	"Youstice Resolution Module" => "Youstice Resolution Module",
-	"Your online justice"	=> "Vaša online justícia",
-	"We help customers and retailers resolve shopping issues quickly and effectively." => "Pomáhame spotrebiteľom a predajcom rýchlo a efektívne riešiť problémy spojené s nakupovaním.",
-	"Youstice is a global online application for customers and retailers"	=> "Youstice je globálna online aplikácia pre spotrebiteľov a predajcov",
-	"It allows quick and efficient communication between shops and customers", "Umožňuje rýchlu a efektívnu komunikáciu medzi obchodmi a kupujúcimi.",
-	"Complaints are resolved in just a few clicks."	=> "Sťažnosti je možné vyriešiť niekoľkými kliknutiami myšou.",
-	"Claims reporting for logged out users is available at" => "Nahlásanie sťažností pre neprihlásených užívateľov je na adrese",
-	"Yes"	    => "Áno",
-	"No"	    => "Nie",
-	"Products"	    => "Produkty",
-	"Services"	    => "Služby",
-	"Settings"	    => "Nastavenia",
-	"Api Key"	    => "Api Kľúč",
-	"Use sandbox environment"   => "Používať testovacie prostredie",
-	"This e-shop sells"	    => "Tento e-shop predáva",
-	"Save"			    => "Uložiť",
-	"Are you sure you want to uninstall?"	=> "Naozaj si želáte odinštalovať?",
-	"Settings were saved successfully."	=> "Nastavenia boli úspešne uložené.",
-	"Invalid API KEY"		=> "Neplatný API kľúč",
-	"Invalid Configuration value"	=> "Neplatná hodnota konifgurácie",
-	"Youstice - show logo"		=> "Youstice - zobraziť logo",
-	"No logo widget anchor found! Please add following code to file"	=> "Nebol nájdený odkaz pre logo! Pridajte, prosím nasledujúci kód do súboru "
-);
Index: config.xml
===================================================================
--- config.xml	(revision 124)
+++ config.xml	(working copy)
@@ -2,7 +2,7 @@
         <module>
             <name>yousticeresolutionsystem</name>
             <displayName><![CDATA[Youstice]]></displayName>
-            <version><![CDATA[1.5.2]]></version>
+            <version><![CDATA[1.5.4]]></version>
             <description><![CDATA[Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks]]></description>
             <author><![CDATA[Youstice]]></author>
             <tab><![CDATA[advertising_marketing]]></tab>
Index: controllers/front/yrs.php
===================================================================
--- controllers/front/yrs.php	(revision 121)
+++ controllers/front/yrs.php	(working copy)
@@ -354,7 +354,7 @@
 		if (empty($order))
 			exit('Operation not allowed');
 
-		$shop_order->setName('Order #'.$order_id);
+		$shop_order->setName('#'.$order_id);
 		$shop_order->setCurrency($currency->iso_code);
 		$shop_order->setPrice((float)$order['total_paid']);
 		$shop_order->setId($order_id);
Index: public/css/admin.css
===================================================================
--- public/css/admin.css	(revision 124)
+++ public/css/admin.css	(working copy)
@@ -160,7 +160,7 @@
     margin: 0 30px 0 4px;
     padding-top: 5px;
     width: auto;
-    min-width: 280px;
+    min-width: 300px;
     text-align: right;
 }
 
@@ -174,7 +174,7 @@
 }
 
 .yContainer .configuration input#reportClaimsPageLink {
-    min-width: 520px
+    min-width: 500px
 }
 
 .yContainer .configuration input {
Index: translations/de.php
===================================================================
--- translations/de.php	(revision 0)
+++ translations/de.php	(working copy)
@@ -0,0 +1,41 @@
+<?php
+
+global $_MODULE;
+$_MODULE = array();
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_b2b4ec0e92fae6508c3f46b09a0b7e2f'] = 'Das Lösungsmodul von Youstice';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_7f230a41a39e75fce233cef5a5df70d2'] = 'Your online justice';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_876f23178c29dc2552c0b48bf23cd9bd'] = 'Sind Sie sicher, dass Sie deinstallieren möchten?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_32de95aa66bb349a024274ca464976d7'] = 'Youstice - show logo';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Einreichen eine Beschwerde';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_13306a17b69205882acc40b178f4d831'] = 'Falls Sie über ein Produkt oder eine Dienstleistung beschweren möchten, folgen Sie bitte diesem Link.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_58e0559207ce95c3954dc8b2b98545df'] = 'Ungültiger API-Schlüssel';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_07e32ec3f558071b2ad09d26d7ff1609'] = 'Einstellungen upgedated.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_fe5d926454b6a8144efce13a44d019ba'] = 'Ungültiger Einstellungswert';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_bafd7322c6e97d25b6299b5d6fe8920b'] = 'Nein';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_93cba07454f06a4a960172bbd6e2a435'] = 'Ja';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_068f80c7519d0528fb08e82137a72131'] = 'Die Produkte';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_992a0f0542384f1ee5ef51b7cf4ae6c4'] = 'Die Dienstleistungen';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_f4f70727dc34561dfde1a3c529b6205c'] = 'Die Einstellungen';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_2e5e61c17add3609c6786a4c9ce1a279'] = 'API-Schlüssel';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_d19edad169336a4cf13add4beaeb25d2'] = 'Sandbox-Umgebung nutzen';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_82b8900b38e3225324989054e37a0fbf'] = 'Diese E-Shop verkauft';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_c9cc8cce247e49bae79f15173ce97354'] = 'Speichern';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_630f6dc397fe74e52d5189e2c80f282b'] = 'Zurück zur Liste';
+$_MODULE['<{yousticeresolutionsystem}prestashop>head_f201289bc22058d41c8b286f2cbb4b8a'] = 'Wir helfen Kunden und Händlern ihre Probleme beim Online-Shopping schnell und effizient zu lösen.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>head_a65f92781ee21b4a31cf6f009ae3ccff'] = 'Youstice ist eine globale Online-applikation für Kunden und Händler.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>head_25f1473d220314253f2f191c7e9827d3'] = 'Sie erlaubt eine schnelle und effiziente Kommunikation zwischen dem Shop und dem Kunden.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>head_b4f2e68eea33e52da5b4c5db7ab77380'] = 'Beschwerden werden mit nur wenigen Klicks gelöst.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>head_b6c4714f4b511de5a07990ba99629410'] = 'Schadenmeldung für Benutzer abgemeldet ist verfügbar unter';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_b58df1ce90fa5cde2dee70b302b83e83'] = '%d Tage %d Stunden';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_14f3016cb987a27bec715b35d1493ae1'] = 'Umzusetzen';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_077e69a60b17455b04bca8d5640d54cf'] = 'Antwort an den Händler';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_7e70acae909b7f468f6c1c1e65b1b362'] = 'Warten auf Entscheidung';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_e57d929e5cb33066906355570477967f'] = 'Eskaliert zu ODR';
+$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_172e1a68c4772ef22b10fc6e1df48de2'] = 'Warten auf Antwort des Verkäufers';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Einreichen eine Beschwerde';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_276896cadf64a515bc1eacb2adda8815'] = 'Möchten Sie eine Beschwerde einreichen und berichten über Ihre Merk Problem? Geben Sie einfach die Details:';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_4049d979b8e6b7d78194e96c3208a5a5'] = 'Auftragsnummer';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_a0bfb8e59e6c13fc8d990781f77694fe'] = 'Fortsetzen';
+$_MODULE['<{yousticeresolutionsystem}prestashop>showbuttons_13c8eac34dca00497a681cb9e2191174'] = 'Möchten Sie eine Beschwerde einreichen?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_0f6331fd0d61bf77f611f6f116b984c1'] = 'Ein Problem melden';
+$_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_f2292f4ae90ed93f013c9668077b9eb1'] = 'Ein Problem nicht mit Ihrer Bestellung berichten';
Index: translations/es.php
===================================================================
--- translations/es.php	(revision 120)
+++ translations/es.php	(working copy)
@@ -2,8 +2,7 @@
 
 global $_MODULE;
 $_MODULE = array();
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_b2b4ec0e92fae6508c3f46b09a0b7e2f'] = 'Módulo de Resolución de Youstice';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_7f230a41a39e75fce233cef5a5df70d2'] = 'Tu justicia en línea';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_104de1beaaf399bdb5ce1d4992b7e2a8'] = 'Youstice';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_876f23178c29dc2552c0b48bf23cd9bd'] = '¿Estás seguro de que quieres desinstalar?';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_32de95aa66bb349a024274ca464976d7'] = 'Youstice - mostrar logotipo';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Presentar una reclamación';
@@ -11,31 +10,27 @@
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_58e0559207ce95c3954dc8b2b98545df'] = 'LLAVE API inválida';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_07e32ec3f558071b2ad09d26d7ff1609'] = 'Las configuraciones se han guardado con éxito.';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_fe5d926454b6a8144efce13a44d019ba'] = 'Valor de configuración inválido';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_bafd7322c6e97d25b6299b5d6fe8920b'] = 'No';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_93cba07454f06a4a960172bbd6e2a435'] = 'Sí';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_068f80c7519d0528fb08e82137a72131'] = 'Productos';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_992a0f0542384f1ee5ef51b7cf4ae6c4'] = 'Servicios';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_f4f70727dc34561dfde1a3c529b6205c'] = 'Configuraciones';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_2e5e61c17add3609c6786a4c9ce1a279'] = 'Llave Api';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_d19edad169336a4cf13add4beaeb25d2'] = 'Utiliza el entorno de prueba sandbox';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_82b8900b38e3225324989054e37a0fbf'] = 'Este comercio electrónico vende';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_c9cc8cce247e49bae79f15173ce97354'] = 'Guardar';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_630f6dc397fe74e52d5189e2c80f282b'] = 'Regresar a la lista';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_f201289bc22058d41c8b286f2cbb4b8a'] = 'Facilitamos la resolución rápida y efectiva de conflictos entre clientes y comerciantes.';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_a65f92781ee21b4a31cf6f009ae3ccff'] = 'Youstice es una plataforma global en línea para clientes y comerciantes';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_25f1473d220314253f2f191c7e9827d3'] = 'Facilita la comunicación rápida y eficaz entre clientes y comerciantes';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_b4f2e68eea33e52da5b4c5db7ab77380'] = 'Las reclamaciones se resuelven con tan solo unos clics.';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_b6c4714f4b511de5a07990ba99629410'] = 'Presentación de reclamaciones para usuarios fuera del sistema disponible en';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_b58df1ce90fa5cde2dee70b302b83e83'] = '%d días  %d horas';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_14f3016cb987a27bec715b35d1493ae1'] = 'A ser ejecutados';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_077e69a60b17455b04bca8d5640d54cf'] = 'Responder al comerciante';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_7e70acae909b7f468f6c1c1e65b1b362'] = 'A la espera de una decisión';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_e57d929e5cb33066906355570477967f'] = 'Elevado a RDL';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_172e1a68c4772ef22b10fc6e1df48de2'] = 'A la espera de la decisión del comerciante';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_b6c4714f4b511de5a07990ba99629410'] = 'Presentación de reclamaciones para usuarios fuera del sistema disponible en';
+$_MODULE['<{yousticeresolutionsystem}prestashop>orderdetail_a240fa27925a635b08dc28c9e4f9216d'] = 'Pedido';
+$_MODULE['<{yousticeresolutionsystem}prestashop>orderdetail_114d63e6a498967eb8b1dad8c902769b'] = 'Productos en tu pedido (%d)';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Presentar una reclamación';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_276896cadf64a515bc1eacb2adda8815'] = '¿Te gustaría presentar una reclamación e informe sobre tu compra? No tienes más que ingresar tus datos a continuación:';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_4049d979b8e6b7d78194e96c3208a5a5'] = 'Número de pedido';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_a0bfb8e59e6c13fc8d990781f77694fe'] = 'Continuar';
 $_MODULE['<{yousticeresolutionsystem}prestashop>showbuttons_13c8eac34dca00497a681cb9e2191174'] = '¿Te gustaría presentar una reclamación?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_b58df1ce90fa5cde2dee70b302b83e83'] = '%d días  %d horas';
 $_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_0f6331fd0d61bf77f611f6f116b984c1'] = 'Notifica un problema';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithcount_1d74348f932d8aedf341914b091625ab'] = '%d expedientes en trámite';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_14f3016cb987a27bec715b35d1493ae1'] = 'A ser ejecutados';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_077e69a60b17455b04bca8d5640d54cf'] = 'Responder al comerciante';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_7e70acae909b7f468f6c1c1e65b1b362'] = 'A la espera de una decisión';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_e57d929e5cb33066906355570477967f'] = 'Elevado a RDL';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_172e1a68c4772ef22b10fc6e1df48de2'] = 'A la espera de la decisión del comerciante';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_edbc35e2558a4b3b2aa47e80096d8dc7'] = 'Problema notificado';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_14f3016cb987a27bec715b35d1493ae1'] = 'A ser ejecutados';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_077e69a60b17455b04bca8d5640d54cf'] = 'Responder al comerciante';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_7e70acae909b7f468f6c1c1e65b1b362'] = 'A la espera de una decisión';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_e57d929e5cb33066906355570477967f'] = 'Elevado a RDL';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_172e1a68c4772ef22b10fc6e1df48de2'] = 'A la espera de la decisión del comerciante';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_edbc35e2558a4b3b2aa47e80096d8dc7'] = 'Problema notificado';
 $_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_f2292f4ae90ed93f013c9668077b9eb1'] = 'Notifica un problema que no esté relacionado con tus pedidos';
Index: translations/sk.php
===================================================================
--- translations/sk.php	(revision 120)
+++ translations/sk.php	(working copy)
@@ -2,8 +2,7 @@
 
 global $_MODULE;
 $_MODULE = array();
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_b2b4ec0e92fae6508c3f46b09a0b7e2f'] = 'Youstice Resolution Module';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_7f230a41a39e75fce233cef5a5df70d2'] = 'Vaša online justícia';
+$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_104de1beaaf399bdb5ce1d4992b7e2a8'] = 'Youstice';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_876f23178c29dc2552c0b48bf23cd9bd'] = 'Naozaj si želáte odinštalovať?';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_32de95aa66bb349a024274ca464976d7'] = 'Youstice - zobraziť logo';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Nahlásiť sťažnosť';
@@ -11,31 +10,55 @@
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_58e0559207ce95c3954dc8b2b98545df'] = 'Neplatný API kľúč';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_07e32ec3f558071b2ad09d26d7ff1609'] = 'Nastavenia boli úspešne uložené.';
 $_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_fe5d926454b6a8144efce13a44d019ba'] = 'Neplatná hodnota konifgurácie';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_bafd7322c6e97d25b6299b5d6fe8920b'] = 'Nie';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_93cba07454f06a4a960172bbd6e2a435'] = 'Áno';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_068f80c7519d0528fb08e82137a72131'] = 'Produkty';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_992a0f0542384f1ee5ef51b7cf4ae6c4'] = 'Služby';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_f4f70727dc34561dfde1a3c529b6205c'] = 'Nastavenia';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_2e5e61c17add3609c6786a4c9ce1a279'] = 'Api Kľǔč';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_d19edad169336a4cf13add4beaeb25d2'] = 'Používať testovacie prostredie';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_82b8900b38e3225324989054e37a0fbf'] = 'Tento e-shop predáva';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_c9cc8cce247e49bae79f15173ce97354'] = 'Uložiť';
-$_MODULE['<{yousticeresolutionsystem}prestashop>yousticeresolutionsystem_630f6dc397fe74e52d5189e2c80f282b'] = 'Naspäť';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_f201289bc22058d41c8b286f2cbb4b8a'] = 'Pomáhame spotrebiteľom a predajcom rýchlo a efektívne riešiť problémy spojené s nakupovaním.';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_a65f92781ee21b4a31cf6f009ae3ccff'] = 'Youstice je globálna online aplikácia pre spotrebiteľov a predajcov';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_25f1473d220314253f2f191c7e9827d3'] = 'Umožňuje rýchlu a efektívnu komunikáciu medzi obchodmi a kupujúcimi.';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_b4f2e68eea33e52da5b4c5db7ab77380'] = 'Sťažnosti je možné vyriešiť niekoľkými kliknutiami myšou.';
-$_MODULE['<{yousticeresolutionsystem}prestashop>head_b6c4714f4b511de5a07990ba99629410'] = 'Nahlásanie sťažností pre neprihlásených užívateľov je na adrese';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_b58df1ce90fa5cde2dee70b302b83e83'] = '%d dní %d hodín';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_14f3016cb987a27bec715b35d1493ae1'] = 'Implementuje sa';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_077e69a60b17455b04bca8d5640d54cf'] = 'Zaslaná odpoveď obchodníkovi';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_7e70acae909b7f468f6c1c1e65b1b362'] = 'Čakanie na rozhodnutie';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_e57d929e5cb33066906355570477967f'] = 'Poslané na ODR';
-$_MODULE['<{yousticeresolutionsystem}prestashop>_placeholder_172e1a68c4772ef22b10fc6e1df48de2'] = 'Čakanie na odpoveď od obchodníka';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_0832c9e4f52eb72d448f8733d805e919'] = 'Riešte zákazníkové sťažnosti v pár kliknutiach.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_cd4ecc74d57b66b70ee670806b604f7b'] = 'Spojte sa s vašimi zákazníkmi';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_22526bb0b9c93d79f33cf48fba04c313'] = 'Osobitný prístup pre každého';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_6db4b1d8814cae12385416d12c4b9314'] = 'Obmedzte negatívne hodnotenia';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_38a254389afa79cfbbe3f2ff54c98f36'] = 'Najskôr rokujte, neskôr hodnoťte';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_69c4b01320434900f3f84789b002e8b0'] = 'Len 2 jednoduché kroky';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_12ee619b94c06286d12c78161fa19ba6'] = 'Rokujte a riešte';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_3062c6eb1216a2fedf1808c9ac1cb363'] = 'Rozšírte svoje podnikanie';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_0c35d8bee06e988658d69ebb6c70352a'] = 'Globálny, viacjazyčný ekosystém';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_63f5306ffbfdcc40a74a388e4b4f25c4'] = 'Pre viac informácii o';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_895d04400b53a40c6f1aeb9b64b6a79f'] = 'Youstice navštívte';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_7adc7fdd22605373506629f7b06e5c70'] = 'Zaberie len pár minút začat s Youstice.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_832dc0a1c2207915db988829e3e4de8d'] = 'Máte už vytvorený účet?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_bafd7322c6e97d25b6299b5d6fe8920b'] = 'Nie';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_93cba07454f06a4a960172bbd6e2a435'] = 'Áno';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_c77f925be110e0b2b13837a3808f82d9'] = 'Registrujte sa pre skúšku zadarmo';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_3aaf6d421ea12bfc96da18c4d635c7b0'] = 'Ak už máte Youstice účet, vyplňte informácie nižšie.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_e46d0a3235888535a5a08c006418b8eb'] = 'Nahlasovanie pre neprihlásených používateľov';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_b6c4714f4b511de5a07990ba99629410'] = 'Nahlásanie sťažností pre neprihlásených užívateľov je na adrese';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_ff03f8fb44278e3222a668caf79648f2'] = 'Nastaviť Youstice pre vašu webstránku';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_0c7526a585b4fd57535b75904c87409f'] = 'Je tento API kľúč pre Živé alebo Vývojárske prostredie?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_2652eec977dcb2a5aea85f5bec235b05'] = 'Vývojárske';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_955ad3298db330b5ee880c2c9e6f23a0'] = 'Živé';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_94ac6ad4916dedfae6c0cb348802178e'] = 'API Kľúč vášho obchodu';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_df20ab8483ccdcf2d8bb275f9d51aafd'] = 'sa zaregistrujte tu';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_e04bff13a5579d1748020acd7d4d413d'] = 'Váš API kľúč môžete nájsť v Youstice aplikácii. Prihláste sa do Youstice.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_e81c4e4f2b7b93b481e13a8553c2ae1b'] = 'alebo';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_6f6071f543aae20188a424d75dfbbdd6'] = 'v menu kliknite na OBCHODY, ďalej na váš obchod a na konci stránky nájdete váš API kľúč.';
+$_MODULE['<{yousticeresolutionsystem}prestashop>main_f5cf47ab06d0d98b0d16d10c82d87953'] = 'ULOŽIŤ';
+$_MODULE['<{yousticeresolutionsystem}prestashop>orderdetail_a240fa27925a635b08dc28c9e4f9216d'] = 'Objednávka';
+$_MODULE['<{yousticeresolutionsystem}prestashop>orderdetail_114d63e6a498967eb8b1dad8c902769b'] = 'Produkty objednávky (%d)';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_37f0b7ec13be14130e5ec53d8a20ab14'] = 'Nahlásiť sťažnosť';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_276896cadf64a515bc1eacb2adda8815'] = 'Chcete podať sťažnosť a nahlásiť váš problém s nakupovaním? Jednoducho vyplňte údaje nižšie:';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_4049d979b8e6b7d78194e96c3208a5a5'] = 'Číslo objednávky';
 $_MODULE['<{yousticeresolutionsystem}prestashop>reportclaims_a0bfb8e59e6c13fc8d990781f77694fe'] = 'Pokračovať';
 $_MODULE['<{yousticeresolutionsystem}prestashop>showbuttons_13c8eac34dca00497a681cb9e2191174'] = 'Chceli by ste nahlásiť sťažnosť?';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_b58df1ce90fa5cde2dee70b302b83e83'] = '%d dní %d hodín';
 $_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_0f6331fd0d61bf77f611f6f116b984c1'] = 'Nahlásiť problém';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithcount_1d74348f932d8aedf341914b091625ab'] = 'Nahlásenia: %d';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_14f3016cb987a27bec715b35d1493ae1'] = 'Implementuje sa';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_077e69a60b17455b04bca8d5640d54cf'] = 'Zaslaná odpoveď obchodníkovi';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_7e70acae909b7f468f6c1c1e65b1b362'] = 'Čakanie na rozhodnutie';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_e57d929e5cb33066906355570477967f'] = 'Poslané na ODR';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_172e1a68c4772ef22b10fc6e1df48de2'] = 'Čakanie na odpoveď od obchodníka';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbutton_edbc35e2558a4b3b2aa47e80096d8dc7'] = 'Problém bol nahlásený';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_14f3016cb987a27bec715b35d1493ae1'] = 'Implementuje sa';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_077e69a60b17455b04bca8d5640d54cf'] = 'Zaslaná odpoveď obchodníkovi';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_7e70acae909b7f468f6c1c1e65b1b362'] = 'Čakanie na rozhodnutie';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_e57d929e5cb33066906355570477967f'] = 'Poslané na ODR';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_172e1a68c4772ef22b10fc6e1df48de2'] = 'Čakanie na odpoveď od obchodníka';
+$_MODULE['<{yousticeresolutionsystem}prestashop>reportedbuttonwithstatus_edbc35e2558a4b3b2aa47e80096d8dc7'] = 'Problém bol nahlásený';
 $_MODULE['<{yousticeresolutionsystem}prestashop>unreportedbutton_f2292f4ae90ed93f013c9668077b9eb1'] = 'Nahlásiť problém netýkajúci sa objednávok';
Index: views/templates/admin/main.tpl
===================================================================
--- views/templates/admin/main.tpl	(revision 124)
+++ views/templates/admin/main.tpl	(working copy)
@@ -24,7 +24,7 @@
 *}
 <form class="yContainer" action="{$saveHref|escape:'false'}" method="post">
     <div class="logoLeft">
-        <img class="logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTUwMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2r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"/>
+        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTUwMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2r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"/>
         <p>{l s='Resolve customer complaints in a few clicks.'|escape:'htmlall' mod='yousticeresolutionsystem'}</p>
     </div>
     <div class="logoRight">
Index: views/templates/front/_placeholder.tpl
===================================================================
--- views/templates/front/_placeholder.tpl	(revision 120)
+++ views/templates/front/_placeholder.tpl	(working copy)
@@ -1,30 +0,0 @@
-{*
-* 2007-2014 PrestaShop
-*
-* NOTICE OF LICENSE
-*
-* This source file is subject to the Academic Free License (AFL 3.0)
-* that is bundled with this package in the file LICENSE.txt.
-* It is also available through the world-wide-web at this URL:
-* http://opensource.org/licenses/afl-3.0.php
-* If you did not receive a copy of the license and are unable to
-* obtain it through the world-wide-web, please send an email
-* to license@prestashop.com so we can send you a copy immediately.
-*
-* DISCLAIMER
-*
-* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
-* versions in the future. If you wish to customize PrestaShop for your
-* needs please refer to http://www.prestashop.com for more information.
-*
-*  @author PrestaShop SA <contact@prestashop.com>
-*  @copyright  2007-2014 PrestaShop SA
-*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
-*  International Registered Trademark & Property of PrestaShop SA
-*}
-{l s='%d days %d hours' sprintf=[2,2]|escape:'htmlall' mod='yousticeresolutionsystem'}
-{l s='To be implemented'|escape:'htmlall' mod='yousticeresolutionsystem'}
-{l s='Respond to retailer'|escape:'htmlall' mod='yousticeresolutionsystem'}
-{l s='Waiting for decision'|escape:'htmlall' mod='yousticeresolutionsystem'}
-{l s='Escalated to ODR'|escape:'htmlall' mod='yousticeresolutionsystem'}
-{l s='Waiting for retailer\'s response'|escape:'htmlall' mod='yousticeresolutionsystem'}
\ No newline at end of file
Index: views/templates/front/orderButton/reportedButton.tpl
===================================================================
--- views/templates/front/orderButton/reportedButton.tpl	(revision 120)
+++ views/templates/front/orderButton/reportedButton.tpl	(working copy)
@@ -22,4 +22,4 @@
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
-<a class="yrsButton yrsOrderButton {$statusClass}" target="_blank" href="{$href}">{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
\ No newline at end of file
+<a class="yrsButton yrsOrderButton {$statusClass}" target="_blank" href="{$href}">{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</a>
\ No newline at end of file
Index: views/templates/front/orderButton/reportedButtonWithStatus.tpl
===================================================================
--- views/templates/front/orderButton/reportedButtonWithStatus.tpl	(revision 120)
+++ views/templates/front/orderButton/reportedButtonWithStatus.tpl	(working copy)
@@ -24,6 +24,6 @@
 *}
 <a class="yrsButton yrsOrderButton yrsButton-with-time {$statusClass|escape:'false'}" target="_blank"
    href="{$href|escape:'false'}">
-    <span>{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
-    <span>{$remainingTime|escape:'htmlall'}</span>
+    <span>{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
+    <span>{{l s='%d days %d hours' sprintf=[$remainingTimeDays, $remainingTimeHours] mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
 </a>
\ No newline at end of file
Index: views/templates/front/orderDetail.tpl
===================================================================
--- views/templates/front/orderDetail.tpl	(revision 120)
+++ views/templates/front/orderDetail.tpl	(working copy)
@@ -22,13 +22,13 @@
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
-<div class="orderDetailWrap"><h1>{$orderName|escape:'htmlall'}</h1>
+<div class="orderDetailWrap"><h1>{l s='Order'|escape:'htmlall' mod='yousticeresolutionsystem'} {$orderName|escape:'htmlall'}</h1>
     <div class="topRightWrap">
         {$orderButton|escape:'false'}
         <span class="space"></span>
         <a class="yrsButton yrsButton-close">x</a>
     </div>
-    <h2>{l s=$productsMessage sprintf=$productsMessageCount|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
+    <h2>{l s='Products in your order (%d)' sprintf=$productsMessageCount|escape:'htmlall' mod='yousticeresolutionsystem'}</h2>
     {if !empty($products)}
         <table class="orderDetail">
 
Index: views/templates/front/orderDetailButton/reportedButton.tpl
===================================================================
--- views/templates/front/orderDetailButton/reportedButton.tpl	(revision 120)
+++ views/templates/front/orderDetailButton/reportedButton.tpl	(working copy)
@@ -22,4 +22,4 @@
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
-<a class="yrsButton yrsOrderDetailButton {$statusClass}" href="{$href}">{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
\ No newline at end of file
+<a class="yrsButton yrsOrderDetailButton {$statusClass}" href="{$href}">{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</a>
\ No newline at end of file
Index: views/templates/front/orderDetailButton/reportedButtonWithCount.tpl
===================================================================
--- views/templates/front/orderDetailButton/reportedButtonWithCount.tpl	(revision 120)
+++ views/templates/front/orderDetailButton/reportedButtonWithCount.tpl	(working copy)
@@ -24,7 +24,7 @@
 *}
 <div class="orderDetailButtonWrap">
     <a class="yrsButton yrsButton-order-detail" 
-       href="{$href}">{l s=$message sprintf=$messageCount|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
+       href="{$href}">{l s='%d ongoing cases' sprintf=$messageCount|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
 
     <a class="yrsButton yrsButton-plus" href="{$href|escape:'false'}">+</a>
 
Index: views/templates/front/orderDetailButton/reportedButtonWithStatus.tpl
===================================================================
--- views/templates/front/orderDetailButton/reportedButtonWithStatus.tpl	(revision 120)
+++ views/templates/front/orderDetailButton/reportedButtonWithStatus.tpl	(working copy)
@@ -24,6 +24,6 @@
 *}
 <a class="yrsButton yrsOrderDetailButton yrsButton-with-time {$statusClass|escape:'false'}" 
    href="{$href|escape:'false'}">
-    <span>{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
-    <span>{$remainingTime|escape:'htmlall'}</span>
+    <span>{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
+    <span>{{l s='%d days %d hours' sprintf=[$remainingTimeDays, $remainingTimeHours] mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
 </a>
\ No newline at end of file
Index: views/templates/front/productButton/reportedButton.tpl
===================================================================
--- views/templates/front/productButton/reportedButton.tpl	(revision 120)
+++ views/templates/front/productButton/reportedButton.tpl	(working copy)
@@ -22,4 +22,4 @@
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
-<a class="yrsButton {$statusClass}" target="_blank" href="{$href}">{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
\ No newline at end of file
+<a class="yrsButton {$statusClass}" target="_blank" href="{$href}">{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</a>
\ No newline at end of file
Index: views/templates/front/productButton/reportedButtonWithStatus.tpl
===================================================================
--- views/templates/front/productButton/reportedButtonWithStatus.tpl	(revision 120)
+++ views/templates/front/productButton/reportedButtonWithStatus.tpl	(working copy)
@@ -24,6 +24,6 @@
 *}
 <a class="yrsButton yrsButton-with-time {$statusClass|escape:'false'}" target="_blank"
    href="{$href|escape:'false'}">
-    <span>{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
-    <span>{$remainingTime|escape:'htmlall'}</span>
+    <span>{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
+    <span>{{l s='%d days %d hours' sprintf=[$remainingTimeDays, $remainingTimeHours] mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
 </a>
\ No newline at end of file
Index: views/templates/front/webButton/reportedButton.tpl
===================================================================
--- views/templates/front/webButton/reportedButton.tpl	(revision 120)
+++ views/templates/front/webButton/reportedButton.tpl	(working copy)
@@ -22,4 +22,13 @@
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
-<a class="yrsButton {$statusClass}" target="_blank" href="{$href}">{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</a>
\ No newline at end of file
+<a class="yrsButton {$statusClass}" target="_blank" href="{$href}">{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</a>
+
+{if false}
+    {l s='To be implemented'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Respond to retailer'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Waiting for decision'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Escalated to ODR'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {{l s='Waiting for retailer\'s response' mod='yousticeresolutionsystem'}|escape:'htmlall'}
+    {l s="Problem reported"|escape:'htmlall' mod='yousticeresolutionsystem'}
+{/if}
\ No newline at end of file
Index: views/templates/front/webButton/reportedButtonWithStatus.tpl
===================================================================
--- views/templates/front/webButton/reportedButtonWithStatus.tpl	(revision 120)
+++ views/templates/front/webButton/reportedButtonWithStatus.tpl	(working copy)
@@ -24,6 +24,15 @@
 *}
 <a class="yrsButton yrsButton-with-time {$statusClass|escape:'false'}" target="_blank"
    href="{$href|escape:'false'}">
-    <span>{l s=$message|escape:'htmlall' mod='yousticeresolutionsystem'}</span>
-    <span>{$remainingTime|escape:'htmlall'}</span>
-</a>
\ No newline at end of file
+    <span>{{l s=$message mod='yousticeresolutionsystem'}|escape:'htmlall'}</span>
+    <span>{l s='%d days %d hours' sprintf=[$remainingTimeDays, $remainingTimeHours] mod='yousticeresolutionsystem'}</span>
+</a>
+
+{if false}
+    {l s='To be implemented'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Respond to retailer'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Waiting for decision'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {l s='Escalated to ODR'|escape:'htmlall' mod='yousticeresolutionsystem'}
+    {{l s='Waiting for retailer\'s response' mod='yousticeresolutionsystem'}|escape:'htmlall'}
+    {l s="Problem reported"|escape:'htmlall' mod='yousticeresolutionsystem'}
+{/if}
\ No newline at end of file
Index: yousticeresolutionsystem.php
===================================================================
--- yousticeresolutionsystem.php	(revision 124)
+++ yousticeresolutionsystem.php	(working copy)
@@ -18,7 +18,7 @@
 	{
 		$this->name                   = 'yousticeresolutionsystem';
 		$this->tab                    = 'advertising_marketing';
-		$this->version                = '1.5.2';
+		$this->version                = '1.5.4';
 		$this->author                 = 'Youstice';
 		$this->need_instance          = 0;
 		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
@@ -27,7 +27,10 @@
 		parent::__construct();
 
 		$this->displayName		= $this->l('Youstice');
+		//preloading string to translation
+		$this->l('Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks');
 		$description = 'Increase customer satisfaction and become a trusted retailer. Negotiate and resolve customer complaints just in a few clicks';
+		//must be translating function or string, on other cases validator screams
 		$this->description		= $this->l($description);
 		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
@@ -133,7 +136,7 @@
 		$smarty->assign('api_key', Configuration::get('YRS_API_KEY'));
 		$smarty->assign('use_sandbox', Configuration::get('YRS_SANDBOX'));
 		$smarty->assign('reportClaimsPageLink', $this->getReportClaimsPageLink());
-		$smarty->assign('cssFile', _PS_BASE_URL_.$this->_path.'public/css/admin.css');
+		$smarty->assign('cssFile', $this->_path.'public/css/admin.css');
 
 		$output .= $smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/main.tpl');
 
