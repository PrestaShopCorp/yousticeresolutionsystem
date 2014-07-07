<?php

/**
 * Represents base class for orders
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice\Reports;

class BaseReport {

    protected $exists = false;
    protected $data = array();

    public function __construct($data = array()) {
        if (isset($data) && is_array($data)) {
            $this->exists = true;
            $this->data = $data;
        }
    }

    public function exists() {
        return $this->exists;
    }

    /**
     * Creating another new report is allowed only on this conditions
     * @return boolean
     */
    public function canCreateNew() {
        if (!$this->exists())
            return true;

        if (strtolower($this->getStatus()) == 'terminated')
            return true;

        if ($this->getStatus() == 'Problem reported')
            return true;

        return false;
    }
    
    public function getStatus() {
        if (count($this->data) && isset($this->data['status'])) {
            return $this->data['status'];
        }

        return "Problem reported";
    }

    public function getRemainingTime() {
        $remainingTime = isset($this->data['remaining_time']) ? $this->data['remaining_time'] : 0;
        
        $actualRemainingTime = $remainingTime - (time() - strtotime($this->data['updated_at']));
        
        return $actualRemainingTime >= 0 ? $actualRemainingTime : 0;
    }
    
    public function toArray() {
        return $this->data;
    }

}
