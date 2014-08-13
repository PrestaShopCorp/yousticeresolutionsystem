<?php
/**
 * Represents one order report.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeReportsOrderReport extends YousticeReportsBaseReport {

	public function orderReportExists()
	{
		return isset($this->data['code']) && isset($this->data['created_at']);
	}

	public function getProducts()
	{
		return isset($this->data['products']) ? $this->data['products'] : array();
	}

	public function getReportedProductsCount()
	{
		return isset($this->data['products']) ? count($this->data['products']) : 0;
	}

	public function getCode()
	{
		if (count($this->data) && isset($this->data['code']))
			return $this->data['code'];

		return $this->data['id'];
	}

	public function getName()
	{
		if (count($this->data) && isset($this->data['name']))
			return $this->data['name'];

		return '';
	}

	public function getFirstProductStatus()
	{
		if (isset($this->data['products']) && count($this->data['products']))
		{
			$status = $this->data['products'][0]['status'];

			return Tools::strlen($status) ? $status : 'Problem reported';
		}

		return '';
	}

}
