<?php
/**
 * Handles localy stored reports
 *
 * @author    Youstice
 * @copyright (c) 2015, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

interface YousticeLocalInterface {

	public function __construct(array $db);

	public function getCachedRemoteReportLink($code);

	public function setCachedRemoteReportLink($code, $link);

	public function getChangedReportStatusesCount();

	public function setChangedReportStatusesCount($value);

	public function getWebReport($user_id);

	public function getProductReport($product_id, $order_code = null);

	public function getOrderReport($order_code, $product_codes = array());

	public function createWebReport($user_id);

	public function createReport($code, $user_id, $remaining_time = 0);

	public function updateReportStatus($code, $status);

	public function updateReportRemainingTime($code, $time);

	public function getReportsByUser($user_id);

	public function install();

	public function uninstall();
}
