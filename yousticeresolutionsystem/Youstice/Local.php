<?php
/**
 * Handles localy stored reports
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

namespace Youstice;

/**
 * Handles localy stored reports
 *
 */
class Local implements LocalInterface {

	private $connection = null;
	private $tablePrefix;
	private $dbDriver;

	/**
	 * Initialize connection
	 * @param array $db credentials for PDO
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $db) {
		if (isset($db['host']) && isset($db['socket'])) {
			throw new Exception("Host and socket can't be specified simultaneously");
		}

		if ($db['driver'] == 'mysqli')
			$db['driver'] = 'mysql';

		$connectionString = $db['driver'] . ":dbname=" . $db['name'] . ";charset=utf8";

		if (isset($db['host']))
			$connectionString .= ";host=" . $db['host'];

		if (isset($db['socket']))
			$connectionString .= ";unix_socket=" . $db['socket'];

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
		$_SESSION['YRS']['report' . $code] = array('remoteLink' => $link);
	}

	public function getChangedReportStatusesCount() {
		return isset($_SESSION['YRS']['changedReportStatusesCount']) ? $_SESSION['YRS']['changedReportStatusesCount'] : 0;
	}

	public function setChangedReportStatusesCount($value) {
		$_SESSION['YRS']['changedReportStatusesCount'] = $value;
	}

	public function getWebReport($userId) {
		$code = "WEB_REPORT__" . $userId;

		$queryCount = "SELECT count(1) count FROM " . $this->tablePrefix . "yrs_reports WHERE code LIKE ?";
		$resultCount = $this->executeQueryFetch($queryCount, array($code . '%'));

		//add count to claim's code
		$code .= '__' . $resultCount['count'];

		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($code));

		return new Reports\WebReport($result);
	}

	public function getProductReport($productId, $orderCode = null) {
		$code = $productId;

		if (isset($orderCode)) {
			$code = $orderCode . "__" . $productId;
		}

		$queryCount = "SELECT count(1) count FROM " . $this->tablePrefix . "yrs_reports WHERE code LIKE ?";
		$resultCount = $this->executeQueryFetch($queryCount, array($code . '__%'));

		//add count to claim's code
		$code .= '__' . $resultCount['count'];

		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($code));

		return new Reports\ProductReport($result);
	}

	public function getOrderReport($orderCode, $productCodes = array()) {
		$queryCount = "SELECT count(1) count FROM " . $this->tablePrefix . "yrs_reports WHERE code REGEXP ?";
		$resultCount = $this->executeQueryFetch($queryCount, array('^' . $orderCode . '__[0-9]*$'));

		//add count to claim's code
		$orderCode .= '__' . $resultCount['count'];

		$query = "SELECT * FROM " . $this->tablePrefix . "yrs_reports WHERE code = ?";

		$result = $this->executeQueryFetch($query, array($orderCode));

		if (count($productCodes)) {

			//get products
			foreach ($productCodes as $code) {

				$found = $this->getProductReport($code);

				if ($found->exists()) {
					$result['products'][] = $found->toArray();
				}
			}
		}

		return new Reports\OrderReport($result);
	}

	public function createWebReport($userId) {
		return $this->createReport("WEB_REPORT__" . $userId, $userId);
	}

	public function createReport($code, $userId, $remainingTime = 0) {
		$queryCount = "SELECT count(1) count FROM " . $this->tablePrefix . "yrs_reports WHERE code REGEXP ?";
		$resultCount = $this->executeQueryFetch($queryCount, array($code . '__[0-9]*$'));

		$code .= '__' . ($resultCount['count'] + 1);

		$stmt = $this->connection->prepare("INSERT INTO " . $this->tablePrefix . "yrs_reports "
				. "(code, user_id, status, remaining_time, created_at, updated_at) VALUES (?, ?, null, ?, ?, ?)");

		try {
			$stmt->execute(array($code, $userId, $remainingTime, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')));
		} catch (\PDOException $e) {
			if ((int) $e->getCode() === 23000) {
				throw new \Exception("Report with code " . $code . " already exists");
			}
		}

		return $code;
	}

	public function updateReportStatus($code, $status) {
		if (!trim($status)) {
			return;
		}

		$stmt = $this->connection->prepare("UPDATE " . $this->tablePrefix . "yrs_reports SET status = ?, updated_at = ? WHERE code = ?");

		return $stmt->execute(array($status, date('Y-m-d H:i:s'), $code));
	}

	public function updateReportRemainingTime($code, $time) {
		if ((int) $time < 0 || $time == null) {
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
				return false;
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
