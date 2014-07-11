<?php
/**
 * Represents one product report.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice\Reports;

class ProductReport extends BaseReport {

    public function getCode() {
        if (count($this->data) && isset($this->data['code']) && trim($this->data['code'])) {
            return $this->data['code'];
        } else
            return $this->data['orderId'] . "__" . $this->data['id'];
    }

    public function setCode($code) {
        $this->data['code'] = $code;
    }

}

