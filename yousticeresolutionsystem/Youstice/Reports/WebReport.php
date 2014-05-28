<?php
/**
 * Represents one web report.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice\Reports;

class WebReport {

	protected $exists = false;
	protected $data = array();

	public function __construct($data = array()) {
		if(isset($data) && is_array($data)) {
			$this->exists = true;
			$this->data = $data;
		}
	}

	public function exists() {
		return $this->exists;
	}

	public function getCode() {
		if (count($this->data) && isset($this->data['code'])) {
			return $this->data['code'];
		}

		return "";
	}

	public function setCode($userId) {
		$this->data['code'] = 'WEB_REPORT__' . $userId;
	}

	public function getStatus() {
		if (count($this->data) && isset($this->data['status'])) {
			return $this->data['status'];
		}

		return "Problem reported";

	}

	public function getRemainingTime() {
		return isset($this->data['remaining_time']) ? $this->data['remaining_time'] : 0;
	}

}
