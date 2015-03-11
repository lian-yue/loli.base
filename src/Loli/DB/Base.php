<?php
/* ************************************************************************** */
/*
/*	Lian Yue
/*
/*	Url: www.lianyue.org
/*	Email: admin@lianyue.org
/*	Author: Moon
/*
/*	Created: UTC 2014-04-09 07:56:37
/*	Updated: UTC 2015-03-11 14:23:36
/*
/* ************************************************************************** */
namespace Loli\DB;
use Loli\Log;
abstract class Base{



	// 主服务器
	private $_masterServers;

	// 主连接
	private $_masterLink;


	// 上次ping时间
	protected $masterPingTime;



 	// 从服务器
	private $_slaveServers;

	// 从连接
	private $_slaveLink;

	// 上次ping时间
	protected $slavePingTime;



	// ping 间隔时间  0 ＝ 不尝试 5 ＝ 5秒一次
	protected $pingInterval = 5;

	// 位置 debug 用的
	protected $explain = false;

	// 连接协议
	protected $protocol;

	// 是否是事务
	protected $inTransaction = false;



	// 是否是运行的 slave
	public $slave = true;



	public function __construct(array $masterServers, array $slaveServers = [], $explain = false) {
		$this->_masterServers = $masterServers;
		$this->_slaveServers = $slaveServers;
		$this->explain = $explain;
	}


	public function link($slave = NULL) {
		if ($slave !== NULL) {
			$this->slave = $slave;
		}

		// 从数据库
		if ($this->slave && $this->_slaveServers && !$this->inTransaction) {


			// 链接从数据库
			if ($this->_slaveLink === NULL) {
				$this->_slaveLink = false;
				shuffle($this->_slaveServers);
				$i = 0;
				foreach($this->_slaveServers as $servers) {
					if ($i > 3) {
						break;
					}
					try {
						$this->_slaveLink = $this->connect($this->parseServers($servers));
						break;
					} catch (\Exception $e) {
						if (!$this->explain) {
							throw $e;
						}
						$this->_slaveLink = false;
					}
					++$i;
				}
				$this->slavePingTime = time();
			}


			// 自动ping
			if ($this->_slaveLink && $this->pingInterval > 0 && ($this->slavePingTime + $this->pingInterval) < time()) {
				$this->slavePingTime = time();
				$this->ping();
			}

			// 从数据库有 返回
			if ($this->_slaveLink) {
				return $this->_slaveLink;
			}
		}





		// 主数据库
		$this->_master = false;

		// 链接主数据库
		if ($this->_masterLink === NULL) {
			$this->_masterLink = false;
			shuffle($this->_masterServers);
			$i = 0;
			foreach ($this->_masterServers as $servers) {
				if ($i > 3) {
					break;
				}
				try {
					$this->_masterLink = $this->connect($this->parseServers($servers));
					break;
				} catch (\Exception $e) {
					if (!$this->explain) {
						throw $e;
					}
					$this->_masterLink = false;
				}
				++$i;
			}
			$this->masterPingTime = time();
		}

		if (!$this->_masterLink) {
			throw new ConnectException('this.link()', 'Master link is unavailable');
		}

		// 自动 ping
		if ($this->pingInterval > 0 && ($this->masterPingTime + $this->pingInterval) < time()) {
			$this->masterPingTime = time();
			$this->ping();
		}
		return $this->_masterLink;
	}


	protected function parseServers($servers) {
		$servers = array_filter(is_array($servers) ? $servers : array_map('trim', explode(',', $servers)));
		if ($servers && !is_int(key($servers))) {
			$servers = [$servers];
		}
		$results = [];
		foreach ($servers as $value) {
			if (!$value) {
				continue;
			}
			if (!is_array($value)) {
				$parse = parse_url($value);
				$value = [];
				foreach (['scheme' => 'protocol', 'host' => 'hostname', 'user' => 'username', 'pass' => 'password', 'path' => 'database'] as $k => $v) {
					if (isset($parse[$k])) {
						$value[$v] = $parse[$k];
					}
				}
			}
			if (empty($value['protocol'])) {
				throw new ConnectException('this.parseServers()', 'The database server protocol can not be empty');
			}
			if (empty($value['database'])) {
				throw new ConnectException('this.parseServers()', 'Database is not selected');
			}
			if (!strpos($value['database'], '.') && !strpos($value['database'], '/') && !strpos($value['database'], '\\')) {
				$value['database'] = ltrim($value['database'], '/');
			}
			$value += ['hostname' => 'localhost', 'username' => 'root', 'password' => NULL];
			$results[] = $value;
		}
		if (!$results) {
			throw new ConnectException('this.parseServers()', 'The database server is empty');
		}
		return $results;
	}

	protected function statement($statement, $tables, $slave = NULL) {
		if ($slave === NULL) {
			$slave = $this->slave;
		}
		if ($tables instanceof Statement) {
			$tables->__construct($this, $statement, $slave);
			return $tables;
		}
		$class = ___CLASS__ . 'Statement';
		return new $class($this, $statement, $tables, $slave);
	}

	public function protocol() {
		if ($this->protocol === NULL) {
			$servers = $this->parseServers(reset($this->_masterServers));
			$this->protocol = reset($servers)['protocol'];
		}
		return $this->protocol;
	}

	public function inTransaction() {
		return $this->inTransaction;
	}

	abstract public function command($command, $slave = NULL);
	abstract public function ping($slave = NULL);
	abstract public function connect(array $servers);
	abstract public function beginTransaction();
	abstract public function commit();
	abstract public function rollBack();
	abstract public function lastInsertID();
	abstract public function key($key);
	abstract public function value($value);


	public function exists($tables) {
		return $this->statement(__METHOD__, $tables, false);
	}
	public function create($tables, array $values, array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->values($values)->options($options);
	}
	public function truncate($tables, array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->options($options);
	}
	public function drop($tables, array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->options($options);
	}
	public function select($tables, array $querys = [], array $options = [], $slave = NULL) {
		return $this->statement(__METHOD__, $tables, $slave)->querys($querys)->options($options);
	}
	public function insert($tables, array $documents = [], array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->documents($documents)->options($options);
	}
	public function update($tables, array $document = [], array $queyrs = [], array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->document($document)->queyrs($queyrs)->options($options);
	}
	public function delete($tables, array $querys = [], array $options = []) {
		return $this->statement(__METHOD__, $tables, false)->queyrs($queyrs)->options($options);
	}
}