<?php
/**
 * Handles localy stored reports
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

/**
 * Handles localy stored reports
 *
 */
class YousticeLocal implements YousticeLocalInterface {

	private $connection = null;
	private $table_prefix;
	private $db_driver;
	private $session;

	/**
	 * Initialize connection
	 * @param array $db credentials for PDO
	 * @throws InvalidArgumentException
	 */
	public function __construct(array $db)
	{
		if (isset($db['host']) && isset($db['socket']))
			throw new Exception("Host and socket can't be specified simultaneously");

		if ($db['driver'] == 'mysqli')
			$db['driver'] = 'mysql';

		$connection_string = $db['driver'].':dbname='.$db['name'].';charset=utf8';

		if (isset($db['host']))
			$connection_string .= ';host='.$db['host'];

		if (isset($db['socket']))
			$connection_string .= ';unix_socket='.$db['socket'];

		try {
			$pdo = new PDO($connection_string, $db['user'], $db['pass'], array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			));
		} catch (PDOException $e) {
			throw new InvalidArgumentException('PDOException thrown with message: '.$e->message);
		}

		$this->connection = $pdo;
		$this->table_prefix = isset($db['prefix']) ? $db['prefix'] : '';
		$this->db_driver = $db['driver'];
	}

	/**
	 * 
	 * @param YousticeProvidersSessionProviderInterface $session
	 * @return YousticeApi
	 */
	public function setSession(YousticeProvidersSessionProviderInterface &$session)
	{
		$this->session = $session;

		return $this;
	}

	/**
	 *
	 * @param string $code
	 * @return string remote link | null
	 */
	public function getCachedRemoteReportLink($code)
	{
		if ($this->session->get('report'.$code) && $this->session->get('report'.$code.'remoteLink'))
			return $this->session->get('report'.$code.'remoteLink');

		return null;
	}

	public function setCachedRemoteReportLink($code, $link)
	{
		$this->session->set('report'.$code.'remoteLink', $link);
	}

	public function getChangedReportStatusesCount()
	{
		return $this->session->get('changedReportStatusesCount') ? $this->session->get('changedReportStatusesCount') : 0;
	}

	public function setChangedReportStatusesCount($value)
	{
		$this->session->set('changedReportStatusesCount', $value);
	}

	public function getWebReport($user_id)
	{
		$code = 'WEB_REPORT__'.$user_id;

		$query_count = 'SELECT count(1) count FROM '.$this->table_prefix.'yrs_reports WHERE code LIKE ?';
		$result_count = $this->executeQueryFetch($query_count, array($code.'%'));

		//add count to claim's code
		$code .= '__'.$result_count['count'];

		$query = 'SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE code = ?';

		$result = $this->executeQueryFetch($query, array($code));

		return new YousticeReportsWebReport($result);
	}

	public function getProductReport($product_id, $order_code = null)
	{
		$code = $product_id;

		if (isset($order_code))
			$code = $order_code.'__'.$product_id;

		$query_count = 'SELECT count(1) count FROM '.$this->table_prefix.'yrs_reports WHERE code LIKE ?';
		$result_count = $this->executeQueryFetch($query_count, array($code.'__%'));

		//add count to claim's code
		$code .= '__'.$result_count['count'];

		$query = 'SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE code = ?';

		$result = $this->executeQueryFetch($query, array($code));

		return new YousticeReportsProductReport($result);
	}

	public function getOrderReport($order_code, $product_codes = array())
	{
		$query_count = 'SELECT count(1) count FROM '.$this->table_prefix.'yrs_reports WHERE code REGEXP ?';
		$result_count = $this->executeQueryFetch($query_count, array('^'.$order_code.'__[0-9]*$'));

		//add count to claim's code
		$order_code .= '__'.$result_count['count'];

		$query = 'SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE code = ?';

		$result = $this->executeQueryFetch($query, array($order_code));

		if (count($product_codes))
		{
			//get products
			foreach ($product_codes as $code)
			{
				$found_report = $this->getProductReport($code);

				if ($found_report->exists())
					$result['products'][] = $found_report->toArray();
			}
		}

		return new YousticeReportsOrderReport($result);
	}

	public function createWebReport($user_id)
	{
		return $this->createReport('WEB_REPORT__'.$user_id, $user_id);
	}

	public function createReport($code, $user_id, $remaining_time = 0)
	{
		$query_count = 'SELECT count(1) count FROM '.$this->table_prefix.'yrs_reports WHERE code REGEXP ?';
		$result_count = $this->executeQueryFetch($query_count, array('^'.$code.'__[0-9]*$'));

		$code .= '__'.($result_count['count'] + 1);

		$stmt = $this->connection->prepare('INSERT INTO '.$this->table_prefix.'yrs_reports 
				(code, user_id, status, remaining_time, created_at, updated_at) VALUES (?, ?, null, ?, ?, ?)');

		try {
			$stmt->execute(array($code, $user_id, $remaining_time, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')));
		} catch (PDOException $e) {
			if ((int)$e->getCode() === 23000)
				throw new Exception('Report with code '.$code.' already exists');
			else
				throw new Exception ();
		}

		return $code;
	}

	public function updateReportStatus($code, $status)
	{
		if (!trim($status))
			return;

		$stmt = $this->connection->prepare('UPDATE '.$this->table_prefix.'yrs_reports SET status = ?, updated_at = ? WHERE code = ?');

		return $stmt->execute(array($status, date('Y-m-d H:i:s'), $code));
	}

	public function updateReportRemainingTime($code, $time)
	{
		if ((int)$time < 0 || $time == null)
			return;

		$stmt = $this->connection->prepare('UPDATE '.$this->table_prefix.'yrs_reports SET remaining_time = ?, updated_at = ? WHERE code = ?');

		return $stmt->execute(array($time, date('Y-m-d H:i:s'), $code));
	}

	public function getReportsByUser($user_id)
	{
		$stmt = $this->connection->prepare('SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE user_id = ?');
		$stmt->execute(array($user_id));

		return $stmt->fetchAll();
	}

	protected function executeQueryFetch($query, array $params)
	{
		$stmt = $this->connection->prepare($query);
		$stmt->execute($params);

		return $stmt->fetch();
	}

	public function install()
	{
		$queries = $this->installPrepareQueries();

		$installed = 0;
		foreach ($queries as $query)
		{
			if ($this->db_driver == 'sqlite')
			{
				$query = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $query);
				$query = str_replace('AUTOINCREMENT PRIMARY KEY', 'PRIMARY KEY AUTOINCREMENT', $query);
				$query = str_replace('INT(11)', 'INTEGER', $query);
			}
			try {
				if ($this->connection->query($query))
					$installed++;
			} catch (PDOException $e) {
				return false;
			}
		}

		return count($queries) == $installed;
	}

	protected function installPrepareQueries()
	{
		return array('CREATE TABLE IF NOT EXISTS `'.$this->table_prefix."yrs_reports`(
		`code` VARCHAR(255) NOT NULL DEFAULT '',
		`user_id` int(10) unsigned NOT NULL,
		`status` VARCHAR(200) NULL,
		`remaining_time` int(10) unsigned DEFAULT NULL,
		`created_at` DATETIME NULL,
		`updated_at` DATETIME NULL,
		PRIMARY KEY (`code`)
		)");
	}

	public function uninstall()
	{
		$this->session->destroy();
		return $this->connection->query('DROP TABLE IF EXISTS `'.$this->table_prefix.'yrs_reports`');
	}

}
