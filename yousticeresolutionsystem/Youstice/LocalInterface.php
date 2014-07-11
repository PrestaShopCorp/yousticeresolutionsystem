<?php
/**
 * Handles localy stored reports
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

interface LocalInterface {
    public function __construct(array $db);
    public function getCachedRemoteReportLink($code);
    public function setCachedRemoteReportLink($code, $link);
    public function getChangedReportStatusesCount();
    public function setChangedReportStatusesCount($value);
    public function getWebReport($userId);
    public function getProductReport($productId, $orderCode = null);
    public function getOrderReport($orderCode, $productCodes = array());
    public function createWebReport($userId);
    public function createReport($code, $userId, $remainingTime = 0);
    public function updateReportStatus($code, $status);
    public function updateReportRemainingTime($code, $time);
    public function getReportsByUser($user_id);
    public function install();
    public function uninstall();
    
}
