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
	private $cached = array();

	/**
	 * Initialize connection
	 * @param array $db credentials for PDO
	 * @throws InvalidArgumentException
	 */
	public function __construct(array $db)
	{
		$connection_string = $this->getConnectionString($db);

		try {
			$pdo = new PDO($connection_string, $db['user'], $db['pass'], array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			));
		} catch (PDOException $e) {
			throw new InvalidArgumentException('PDOException was thrown while connecting');
		}

		$this->connection = $pdo;
		$this->table_prefix = isset($db['prefix']) ? $db['prefix'] : '';
		$this->db_driver = $db['driver'];
	}
	
	private function getConnectionString(array $db)
	{
		if (isset($db['host']) && isset($db['socket']))
			throw new Exception("Host and socket can't be specified simultaneously");

		if ($db['driver'] == 'mysqli')
			$db['driver'] = 'mysql';
		
		//host can be socket, check first param of mysql_connect
		if(isset($db['host']) && $db['host'][0] == ':')
		{
			$db['socket'] = $db['host'];
			$db['host'] = null;
		}
			

		$connection_string = $db['driver'].':dbname='.$db['name'];
		
		if($db['driver'] == 'mysql')
			$connection_string .= ';charset=utf8';

		if (isset($db['host']))
			$connection_string .= ';host='.$db['host'];

		if (isset($db['port']))
			$connection_string .= ';port='.$db['port'];

		if (isset($db['socket']))
			$connection_string .= ';unix_socket='.$db['socket'];
		
		return $connection_string;
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
		if ($this->session->get('report'.$code.'remoteLink'))
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
		
		$result = $this->getReport($code.'__%');

		return new YousticeReportsWebReport($result);
	}

	public function getProductReport($product_id, $order_code = null)
	{
		$code = $product_id;

		if (isset($order_code))
			$code = $order_code.'__'.$product_id;
		
		$result = $this->getReport($code.'__%');

		return new YousticeReportsProductReport($result);
	}

	public function getOrderReport($order_code, $product_codes = array())
	{
		$result = $this->getReport('^'.$order_code.'__[0-9]*$', true);

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
	
	protected function getReport($searchValue, $useRegexp = false) {
		if(isset($this->cached[$searchValue]))
			return $this->cached[$searchValue];

		$searchBy = $useRegexp ? "REGEXP" : "LIKE";
		
		//try to find filled report
		$query_filled = 'SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE code '.$searchBy.' ? AND status IS NOT NULL ORDER BY created_at DESC, code DESC LIMIT 1';
		$query_res = $this->executeQueryFetch($query_filled, array($searchValue));

		//otherwise select last
		if(!$query_res) {
			$query_last = 'SELECT * FROM '.$this->table_prefix.'yrs_reports WHERE code '.$searchBy.' ? ORDER BY created_at DESC, code DESC LIMIT 1';
			
			$query_res = $this->executeQueryFetch($query_last, array($searchValue));
		}
		
		return $this->cached[$searchValue] = $query_res;
	}

	public function createWebReport($user_id)
	{
		return $this->createReport('WEB_REPORT__'.$user_id, $user_id);
	}

	public function createReport($code, $user_id, $remaining_time = 0)
	{
		$this->connection->beginTransaction();
		$this->lockTable();
		
		$query_count = $this->prepareRegexpQuery('SELECT count(1) count FROM '.$this->table_prefix.'yrs_reports WHERE code REGEXP ?');
		$result_count = $this->executeQueryFetch($query_count, array('^'.$code.'__[0-9]*$'));

		$code .= '__'.($result_count['count'] + 1);

		$stmt = $this->connection->prepare('INSERT INTO '.$this->table_prefix.'yrs_reports 
				(code, user_id, status, remaining_time, created_at, updated_at) VALUES (?, ?, null, ?, NOW(), NOW())');

		try {
			$stmt->execute(array($code, $user_id, $remaining_time));
			
			$this->unlockTable();
			$this->connection->commit();
		} catch (PDOException $e) {
			$this->connection->rollBack();
			
			if ((int)$e->getCode() === 23000)
				throw new Exception('Report with code '.$code.' already exists');
			else
				throw new Exception ('Creating report failed');
		}

		return $code;
	}

	public function updateReportStatus($code, $status)
	{
		if (!trim($status))
			return;

		$stmt = $this->connection->prepare('UPDATE '.$this->table_prefix.'yrs_reports SET status = ?, updated_at = NOW() WHERE code = ?');

		return $stmt->execute(array($status, $code));
	}

	public function updateReportRemainingTime($code, $time)
	{
		if ((int)$time < 0 || $time == null)
			return;

		$stmt = $this->connection->prepare('UPDATE '.$this->table_prefix.'yrs_reports SET remaining_time = ?, updated_at = NOW() WHERE code = ?');

		return $stmt->execute(array($time, $code));
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
	
	protected function prepareRegexpQuery($query = '')
	{
		if ($this->db_driver === 'pgsql')
			$query = str_replace('REGEXP', '~', $query);
		
		return $query;
	}
	
	protected function lockTable()
	{
		if ($this->db_driver == 'pgsql')
		{
			$this->connection->exec('LOCK TABLE '.$this->table_prefix.'yrs_reports IN ACCESS EXCLUSIVE MODE');
		}
		else
		{
			$this->connection->exec('LOCK TABLES '.$this->table_prefix.'yrs_reports WRITE');
		}
	}
	
	protected function unlockTable()
	{
		if($this->db_driver !== 'pgsql')
			$this->connection->exec('UNLOCK TABLES');
	}

	public function install()
	{
		$queries = $this->installPrepareQueries();

		$installed = 0;
		foreach ($queries as $query)
		{
			if ($this->db_driver == 'pgsql')
			{
				$query = str_replace('remaining_time INTEGER DEFAULT NULL', 'remaining_time INTEGER', $query);
				$query = str_replace('int(10) unsigned', 'INTEGER', $query);
				$query = str_replace('DATETIME', 'timestamp without time zone', $query);
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
		return array('CREATE TABLE IF NOT EXISTS '.$this->table_prefix."yrs_reports(\n
			code VARCHAR(255) NOT NULL DEFAULT '',
			user_id int(10) unsigned NOT NULL,
			status VARCHAR(200) NULL,
			remaining_time int(10) unsigned DEFAULT NULL,
			created_at DATETIME NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (code)
		)");
	}

	public function uninstall()
	{
		$this->session->destroy();
		return $this->connection->query('DROP TABLE IF EXISTS `'.$this->table_prefix.'yrs_reports`');
	}

}
