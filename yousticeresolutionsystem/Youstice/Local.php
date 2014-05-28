<?php
/**
 * Handles localy stored reports
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace Youstice;

/**
 * Handles localy stored reports
 *
 */
class Local {

	private $connection = null;
	private $tablePrefix;
	private $dbDriver;

	/**
	 * Initialize connection
	 * @param array $db
	 * @throws \InvalidArgumentException
	 */
	public function __construct($db) {
		$connectionString = $db['driver'] . ":host=" . $db['host']
				. ";dbname=" . $db['name'] . ";charset=utf8";

		try {
			$pdo = new \PDO($connectionString, $db['user'], $db['pass'], array(
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			));
		} catch (PDOException $e) {
			throw new \InvalidArgumentException("PDOException thrown with message: " . $e->message);
		}

		$this->connection = $pdo;
		$this->tablePrefix = isset($db['prefix']) ? $db['prefix'] : '';
		$this->dbDriver = $db['driver'];
	}

	/**
	 *
	 * @param string $code
	 * @return string remote link | null
	 */
	public function getCachedRemoteReportLink($code) {
		if (isset($_SESSION['YRS']['report' . $code]) && strlen($_SESSION['YRS']['report' . $code]['remoteLink'])) {
			return $_SESSION['YRS']['report' . $code]['remoteLink'];
		}

		return null;
	}

	public function setCachedRemoteReportLink($code, $link) {
		$_SESSION['YRS']['report'.$code] = array('remoteLink' => $link);
	}

	public function getChangedReportStatusesCount() {
		return isset($_SESSION['YRS']['changedReportStatusesCount'])
					? $_SESSION['YRS']['changedReportStatusesCount']
					: 0;
	}

	public function setChangedReportStatusesCount($value) {
		$_SESSION['YRS']['changedReportStatusesCount'] = $value;
	}

	public function getWebReport($userId) {
		$code = "WEB_REPORT__" . $userId;

		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($code));

		return new Reports\WebReport($result);
	}

	public function getProductReport($productId, $orderCode = null) {
		$code = $productId;

		if(isset($orderCode)) {
			$code = $orderCode . "__" . $productId;
		}

		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($code));

		return new Reports\ProductReport($result);
	}

	public function getOrderReport($orderCode, $productIds = array()) {
		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($orderCode));

		$productCodes = array();
		if (count($productIds)) {
			// generate "orderId__productId" for each product
			foreach ($productIds as $productId) {
				$productCodes[] = str_replace("ORDER__", "", $orderCode) . "__" . $productId;
			}

			//get products
			foreach($productCodes as $code) {
				$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

				$found = $this->executeQueryFetch($query, array($code));

				if($found !== false) {
					$result['products'][] = $found;
				}
			}

		}

		return new Reports\OrderReport($result);
	}

	public function createReport($code, $userId, $remainingTime = 0) {
		$stmt = $this->connection->prepare("INSERT INTO " . $this->tablePrefix . "yrs_reports "
				. "(code, user_id, status, remaining_time, created_at, updated_at) VALUES (?, ?, null, ?, ?, ?)");

		try {
			$result = $stmt->execute(array($code, $userId, $remainingTime, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')));
		}
		catch(\PDOException $e) {
			if((int)$e->getCode() === 23000) {
				throw new \Exception("Report with code " . $code . " already exists");
			}
		}

		return $result;
	}

	public function updateReportStatus($code, $status) {
		if (!trim($status)) {
			return;
		}

		$stmt = $this->connection->prepare("UPDATE " . $this->tablePrefix . "yrs_reports SET status = ?, updated_at = ? WHERE code = ?");

		return $stmt->execute(array($status, date('Y-m-d H:i:s'), $code));
	}

	public function updateReportRemainingTime($code, $time) {
		if((int)$time < 0 || $time == null) {
			return;
		}

		$stmt = $this->connection->prepare("UPDATE " . $this->tablePrefix . "yrs_reports SET remaining_time = ?, updated_at = ? WHERE code = ?");

		return $stmt->execute(array($time, date('Y-m-d H:i:s'), $code));
	}

	public function getReportsByUser($user_id) {
		$stmt = $this->connection->prepare("SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE user_id = ?");
		$stmt->execute(array($user_id));

		return $stmt->fetchAll();
	}

	protected function executeQueryFetch($query, array $params) {
		$stmt = $this->connection->prepare($query);
		$stmt->execute($params);

		return $stmt->fetch();
	}

	public function install() {
		$queries = $this->installPrepareQueries();

		$installed = 0;
		foreach ($queries as $query) {
			if ($this->dbDriver == 'sqlite') {
				$query = str_replace("AUTO_INCREMENT", "AUTOINCREMENT", $query);
				$query = str_replace("AUTOINCREMENT PRIMARY KEY", "PRIMARY KEY AUTOINCREMENT", $query);
				$query = str_replace("INT(11)", "INTEGER", $query);
			}
			try {
				if ($this->connection->query($query)) {
					$installed++;
				}
			} catch (\PDOException $e) {
				var_dump($e);
				exit('Install error');
			}
		}

		return count($queries) == $installed;
	}

	protected function installPrepareQueries() {
		return array("CREATE TABLE IF NOT EXISTS `" . $this->tablePrefix . "yrs_reports`(
		`code` VARCHAR(255) NOT NULL DEFAULT '',
		`user_id` int(10) unsigned NOT NULL,
		`status` VARCHAR(200) NULL,
		`remaining_time` int(10) unsigned DEFAULT NULL,
		`created_at` DATETIME NULL,
		`updated_at` DATETIME NULL,
		PRIMARY KEY (`code`)
		)");
	}

	public function uninstall() {
		@$_SESSION['YRS'] = null;
		return $this->connection->query("DROP TABLE IF EXISTS `" . $this->tablePrefix . "yrs_reports`");
	}

}
