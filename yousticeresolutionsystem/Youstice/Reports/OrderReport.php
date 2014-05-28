<?php
/**
 * Represents one order report.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Reports;

class OrderReport {

	protected $exists = false;
	protected $data = array();

	public function __construct($data) {
		if (isset($data) && is_array($data) && count($data)) {
			$this->exists = true;
			$this->data = $data;
		}
	}

	//at least one report
	public function exists() {
		return $this->exists;
	}

	public function orderReportExists() {
		return isset($this->data['code']) && isset($this->data['created_at']);
	}

	public function getProducts() {
		return isset($this->data['products']) ? $this->data['products'] : array();
	}

	public function getReportedProductsCount() {
		return isset($this->data['products']) ? count($this->data['products']) : 0;
	}

	public function getCode() {
		if (count($this->data) && isset($this->data['code'])) {
			return $this->data['code'];
		}

		return $this->data['id'];
	}

	public function getName() {
		if (count($this->data) && isset($this->data['name'])) {
			return $this->data['name'];
		}

		return "";
	}

	public function getStatus() {
		if (count($this->data) && isset($this->data['status']))
			return $this->data['status'];

		return "Problem reported";
	}

	public function getRemainingTime() {
		return isset($this->data['remaining_time']) ? $this->data['remaining_time'] : 0;
	}

	public function getFirstProductStatus() {
		if ( isset($this->data['products']) && count($this->data['products']) ) {
			$status = $this->data['products'][0]['status'];

			return strlen($status) ? $status : 'Problem reported';
		}

		return "";
	}

}
