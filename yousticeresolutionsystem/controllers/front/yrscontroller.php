<?php
/**
 * Main Youstice Resolution system frontend controller.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YrsController extends FrontController {

	private $yapi = null;
	public $theme_file = '';

	const URL_ORDERS = 'index.php?controller=history';
	const URL_YRS = 'modules/yousticeresolutionsystem/';

	public function __construct(\Youstice\Api $yapi)
	{
		parent::__construct();

		$yapi->setUserId($this->context->customer->id);
		$yapi->run();
		$this->yapi = $yapi;
	}
        
        public function getShowButtonsHtml()
        {
            echo $this->yapi->getShowButtonsWidgetHtml();
        }

	public function logoWidget()
	{
		echo $this->yapi->getLogoWidgetHtml();
	}

	public function setButtonsVisible()
	{
		$this->yapi->setButtonsVisible();
		$this->yapi->orderHistoryViewed();
		header('Location: '._PS_BASE_URL_.__PS_BASE_URI__.self::URL_ORDERS);
		exit;
	}

	# AJAX
	public function getOrdersButtons($in)
	{
		$reports = array();
		foreach ($in['order_ids'] as $order_id)
		{
			$order = $this->getOrder($order_id);
			if ($order->id_customer != $this->context->customer->id)
				continue;

			$shop_order = $this->createShopOrder($order_id);

			$reports[$order_id] = $this->yapi->getOrderDetailButtonHtml(
					self::URL_YRS.'index.php?section=getOrderDetail&order_id='.$order_id, $shop_order
			);
		}

		echo Tools::jsonEncode($reports);
	}

	# AJAX
	public function getProductsButtons($in)
	{
		$reports = array();
		$order_id = $in['order_id'];

		foreach ($in['products_ids'] as $product_order_id)
		{
			$order = $this->getOrder($order_id);
			if ($order->id_customer != $this->context->customer->id)
				continue;

			$href = self::URL_YRS.'index.php?section=productReportPost&order_id='.$order_id.'&amp;id_order_detail='.$product_order_id;

			$reports[$product_order_id] = $this->yapi->getProductReportButtonHtml($href, $product_order_id, $order_id);
		}

		echo Tools::jsonEncode($reports);
	}

	# AJAX
	public function getWebReportButton()
	{
		if (!$this->context->customer->id)
			return;

		echo $this->yapi->getWebReportButtonHtml('modules/yousticeresolutionsystem/index.php?section=webReportPost');
	}

	# RENDERS INSIDE POPUP
	public function webReportPost()
	{
		$redirect_url = $this->yapi->createWebReport();

		header('Location: '.$redirect_url);
		exit;
	}

	public function getOrderDetail($in)
	{
		$shop_order = $this->createShopOrder($in['order_id']);

		echo $this->yapi->getOrderDetailHtml($shop_order);
	}

	public function orderReportPost($in)
	{
		$shop_order = $this->createShopOrder((int)$in['order_id']);

		$redirect_link = $this->yapi->createOrderReport($shop_order);

		header('Location: '.$redirect_link);
		exit;
	}

	public function productReportPost($in)
	{
		$shop_order = $this->createShopOrder((int)$in['order_id']);

		$shop_products = $shop_order->getProducts();

		foreach ($shop_products as $shop_product)
		{
			if ($shop_product->getId() == $in['id_order_detail'])
			{
				$redirect_link = $this->yapi->createProductReport($shop_product);

				header('Location: '.$redirect_link);
				exit;
			}
		}

		exit('Product not found');
	}

	private function getOrder($order_id)
	{
		$order = null;
		if ($orders = Order::getCustomerOrders($this->context->customer->id))
		{
			foreach ($orders as &$o)
			{
				if ($order_id == (int)$o['id_order'])
				{
					$order = new Order((int)$o['id_order']);
					if (Validate::isLoadedObject($order))
						$o['virtual'] = $order->isVirtual(false);
				}
			}
		}
		return $order;
	}

	private function buildDataArray($order = null, $product_obj = null)
	{
		$request_data = array(
			'shop' => array(
				'name' => $this->context->shop->name,
				'theme_name' => $this->context->shop->theme_name,
				'domain' => $this->context->shop->domain,
				'domain_ssl' => $this->context->shop->domain_ssl,
			),
			'customer' => array(
				'lastname' => $this->context->customer->lastname,
				'firstname' => $this->context->customer->firstname,
				'birthday' => $this->context->customer->birthday,
				'email' => $this->context->customer->email,
			),
			'language' => array(
				'name' => $this->context->language->name,
				'code' => $this->context->language->language_code,
				'iso_code' => $this->context->language->iso_code,
			)
		);

		if ($order)
		{
			$date_invoice = null;
			$date_delivery = null;
			if ($order->invoice_date > 0)
			{
				$date_invoice = new DateTime($order->invoice_date);
				$date_invoice = $date_invoice->format(DateTime::ISO8601);
			}
			if ($order->delivery_date > 0)
			{
				$date_delivery = new DateTime($order->delivery_date);
				$date_delivery = $date_delivery->format(DateTime::ISO8601);
			}
			$request_data['order'] = array(
				'id' => $order->id,
				'payment' => $order->payment,
				'reference' => $order->reference,
				'delivery_date' => $date_delivery,
				'invoice_date' => $date_invoice,
				'date_add' => $order->date_add,
				'date_upd' => $order->date_upd,
				'total_paid' => $order->total_paid,
				'total_paid_tax_incl' => $order->total_paid_tax_incl,
				'total_paid_tax_excl' => $order->total_paid_tax_excl,
				'currency' => Currency::getCurrencyInstance((int)$order->id_currency),
			);
		}

		if ($product_obj)
		{
			$supplier_name = Supplier::getNameById($product_obj->id_supplier);
			$request_data['product'] = array(
				'id' => $product_obj->id,
				'name' => $product_obj->name,
				'description' => $product_obj->description,
				'description_short' => $product_obj->description_short,
				'price' => $product_obj->price,
				'reference' => $product_obj->reference,
				'ean13' => $product_obj->ean13,
				'width' => $product_obj->width,
				'height' => $product_obj->height,
				'depth' => $product_obj->depth,
				'weight' => $product_obj->weight,
				'supplier_name' => $supplier_name,
			);
		}
		return $request_data;
	}

	public function createShopOrder($order_id)
	{
		$order = $this->getOrder($order_id);
		$products = $order->getProducts();
		$currency = Currency::getCurrencyInstance((int)$order->id_currency);

		$shop_order = \Youstice\ShopOrder::create();
		$shop_order->setDescription('not provided');
		$shop_order->setName('Order #'.$order_id);
		$shop_order->setCurrency($currency->iso_code);
		$shop_order->setPrice((float)$order->total_paid);
		$shop_order->setId($order_id);
		$shop_order->setDeliveryDate($order->delivery_date);
		$shop_order->setOrderDate($order->date_add);
		//$shop_order->setImage(NULL);
		$shop_order->setOtherInfo(Tools::jsonEncode($this->buildDataArray($order)));
		$shop_order->setHref(self::URL_YRS.'index.php?section=orderReportPost&amp;order_id='.$order_id);

		foreach ($products as $product)
		{
			$shop_product = $this->createShopProduct($product, $order_id);
			$shop_product->setCurrency($currency->iso_code);
			$shop_product->setDeliveryDate($order->delivery_date);
			$shop_product->setOrderDate($order->date_add);

			$shop_order->addProduct($shop_product);
		}

		return $shop_order;
	}

	public function createShopProduct(array $product, $order_id)
	{
		$shop_product = Youstice\ShopProduct::create();
		$shop_product->setName($product['product_name']);
		$shop_product->setId($product['id_order_detail']);
		$shop_product->setPrice((float)$product['unit_price_tax_incl']);

		$product_obj = new Product($product['product_id'], false, Context::getContext()->language->id);
		$shop_product->setDescription($product_obj->description);
		$shop_product->setOtherInfo(Tools::jsonEncode($this->buildDataArray(null, $product_obj)));

		//add image if exists
		if (count($product['image']->id_image) > 0)
		{
			$image_path = _PS_PROD_IMG_DIR_.$product['image']->getExistingImgPath().'.jpg';
			$shop_product->setImagePath($image_path);
		}

		$shop_product->setOrderId($order_id);
		$shop_product->setHref(self::URL_YRS.'index.php?section=productReportPost&amp;order_id='.$order_id
				.'&amp;id_order_detail='.$product['id_order_detail']);

		return $shop_product;
	}

}
