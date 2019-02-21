<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 数据库类：数据库主从、事务、日志及监控									      |
 * | 		PDO_MySQL (高可用配合DNS和数据库中间件使用) 暂时未加连接重试           |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Db;

class Pdo
{
	//主库标识
	const M = 'master';
	
	//从库标识
	const S = 'slave';
	
	//method
	const Q = 'QUERY';
	
	//数据库错误
	const PDO_ERROR = 'pdo_error';
	
	/**
	 * db 配置
	 * @var array
	 */
	protected $_conf = array();

	/**
	 * db 连接资源句柄对象
	 * @var unknown
	 */
	protected $_dbh = null;

	/**
	 * 是否开启事物或在事务中
	 * @var string
	 */
	protected $_bolTrans = false;

	/**
	 * db池
	 * @var array
	 */
	protected $_dbPool = array();
	
	/**
	 * 当前执行的SQL
	 * @var unknown
	 */
	protected $_sql = null;
	
	/**
	 * 执行sql语句方法
	 * @var array
	 */
	protected static $_sqlMethods = array('exec','query','prepare');

	/**
	 * 是否强制主库
	 * @var string
	 */
	protected $_forceMaster = false;

	/**
	 * statement对象
	 * @var unknown
	 */
	protected $_statement = null;
	
	/**
	 * 工具辅助类对象
	 * @var unknown
	 */
	protected $_assist = null;

	/**
	 * Pdo类初始化
	 * @param array $config
	 * @throws \Pol\Exception\PdoFailed
	 */
	public function __construct(array $config)
	{
		if ( !class_exists('\PDO') ) {
			throw new \Pol\Exception\PdoFailed('class \\PDO is not exists');
		}
		
		if ( !isset($config[self::M]) || !isset($config[self::S]) ) {
			throw new \Pol\Exception\PdoFailed('PdoConfigIsError:'.json_encode($this->_conf));
		}
		
		$this->_conf = $config;
	}

	/**
	 * 创建PDO连接
	 *
	 * @param $config
	 * @return null|\PDO
	 * @throws \Pol\Exception\PdoFailed || \PDOException
	 */
	protected function connect($config)
	{
		$host 		= isset($config['host']) 		? $config['host'] 		: '';
		$port 		= isset($config['port']) 		? $config['port'] 		: 3306;
		$dbname 	= isset($config['dbname']) 		? $config['dbname'] 	: '';
		$username 	= isset($config['username']) 	? $config['username'] 	: '';
		$password 	= isset($config['password']) 	? $config['password'] 	: '';
		$charset 	= isset($config['charset']) 	? $config['charset'] 	: 'utf8';
		$options 	= isset($config['options']) 	? $config['options'] 	: array();
		if ( empty($host) || empty($username) || empty($dbname) ) {
			throw new \Pol\Exception\PdoFailed('configIsError:'.json_encode($config));
		}
		
		$link = null;
		try {
			$startTime = microtime(true);
			$dsn = 'mysql:host='.$host.';port='.$port.';dbname='.$dbname.';charset='.$charset;
			//设置连接超时时间为2秒
			$options[\PDO::ATTR_TIMEOUT] = isset($options[\PDO::ATTR_TIMEOUT]) ? $options[\PDO::ATTR_TIMEOUT] : 2;
			//设置PDO的错误处理模式
			$options[\PDO::ATTR_ERRMODE] = isset($options[\PDO::ATTR_ERRMODE]) ? $options[\PDO::ATTR_ERRMODE] : \PDO::ERRMODE_EXCEPTION;
			//设置字符集
			$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES '.$charset;
			$link = new \PDO($dsn, $username, $password,$options);
			$endTime = microtime(true);
			$runTime = 1000 * floatval($endTime - $startTime);
			$runTime = sprintf('%.3f',$runTime);
			unset($config['password']);//安全层面密码不打印日志
			//连接时间超过100毫秒则记录日志
			if( $runTime > 100 ) {
				$log = array('tip'=>'pdoConnect time is too long','config'=>$config,'runTime'=>$runTime);
				\Pol\Log\PolLog::PdoLog()->warning($log);
			}
			if( version_compare(PHP_VERSION,'5.3.6') <= 0 ) {
				$link->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); //禁用prepared statements的仿真效果
			}
		} catch ( \PDOException $e ) {
			unset($config['password']);//安全层面密码不打印日志
			$log = array('tip'=>'pdoConnect catch Exception','config'=>$config,
						 'PDOExceptionCode'=>$e->getCode(),'PdoExceptionMsg'=>$e->getMessage());
			\Pol\Log\PolLog::PdoLog()->error($log);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::DB_CONNECT_EXCEPTION);
			throw $e;
		}
		return $link;
	}

	/**
	 * 获取数据库连接
	 *
	 * @param string $type
	 * @return null|\PDO
	 * @throws \Pol\Exception\PdoFailed(
	 */
	protected function getCon($type = self::S)
	{
		//连接已创建则直接返回
		if ( isset($this->_dbPool[$type]) ) {
			$this->_dbh = $this->_dbPool[$type];
			return $this->_dbh;
		}

		//创建新的连接
		$config 	= $this->_conf[$type];
		$link = $this->connect($config);
		if( $link ) {
			$this->_dbPool[$type] = $link;
			$link = null;
		}
		
		if ( isset($this->_dbPool[$type]) ) {
			$this->_dbh = $this->_dbPool[$type];
		}
		
		return $this->_dbh;
	}

	/**
	 * 判断主从库
	 *
	 * @param $method
	 * @param $arguments
	 * @return bool
	 */
	protected function isUseMaster($method, $arguments)
	{
		$userMaster = false;
		if ( in_array($method,self::$_sqlMethods) ) {
			$this->_sql = (isset($arguments[0]) && is_string($arguments[0])) ? ltrim($arguments[0]) : '';
		}
		
		if ( $this->_bolTrans || $this->_forceMaster ) {
			$this->_forceMaster = false;
			$userMaster = true;
			return $userMaster;
		}

		$sql = strtoupper($this->_sql);
		$sql = trim($sql);
		//非查询语句则用主库
		if ( $sql && !preg_match('/^SELECT/', $sql) ) {
			$userMaster = true;
		}
		$this->_forceMaster = false;
		return $userMaster;
	}

	/**
	 * 所有原生Pdo方法调用的入口
	 * @param unknown $method
	 * @param unknown $arguments
	 * @throws \Pol\Exception\PdoFailed
	 * @return \Pol\Db\unknown|\PDOStatement|mixed
	 */
	public function __call($method, $arguments)
	{
		$isMaster = $this->isUseMaster($method, $arguments);
		if ( $isMaster ) {
			$this->getCon(self::M);
		} else {
			$this->getCon(self::S);
		}
		if ( !is_object($this->_dbh) || !method_exists($this->_dbh,$method) ) {
			$log = array('tip'=>'PDO is null or unknown method','errorMethod'=>__METHOD__,'method'=>$method,'args'=>$arguments);
			\Pol\Log\PolLog::PdoLog()->error($log);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::DB_METHOD_UNKNOWN);
			throw new \Pol\Exception\PdoFailed('dbMethodCallFailed,method:'.$method.',args:'.json_encode($arguments));
		}

		$result = call_user_func_array(array($this->_dbh,$method),$arguments);
		if ( $result instanceof \PDOStatement ) {
			if ( !($this->_statement instanceof Statement) ) {
				$this->_statement = new Statement();
			}
			$this->_statement->setStatement($result,$this->_sql);
			return $this->_statement;
		}
		return $result;
	}

	/**
	 * 切换主库
	 *
	 * @return $this
	 */
	public function forceMaster()
	{
		$this->_forceMaster = true;
		return $this;
	}

	/**
	 * 开启事务
	 * @return bool
	 */
	public function beginTransaction()
	{
		if ( !$this->_dbh || !$this->_bolTrans ) {
			$this->getCon(self::M);
		}
		if( $this->_bolTrans ) {
			return true;
		}
		$result = $this->_dbh->beginTransaction();
		if ( $result ) {
			$this->_bolTrans = true;
		}
		return $result;
	}

	/**
	 * 事务提交
	 * @return bool
	 */
	public function commit()
	{
		if ( !$this->_dbh ) {
			throw new \Pol\Exception\PdoFailed('db handler is null when transaction commit');
		}
		$result = $this->_dbh->commit();
		if ( $result ) {
			$this->_bolTrans = false;
		}
		return $result;
	}

	/**
	 * 事务回滚
	 * @return mixed
	 */
	public function rollBack()
	{
		if ( ! $this->_dbh) {
			throw new \Pol\Exception\PdoFailed('db handler is null when transaction rollback');
		}
		$result = $this->_dbh->rollBack();
		if ( $result ) {
			$this->_bolTrans = false;
		}
		return $result;
	}
	
	/**
	 * 删除连接池 脚本延迟运行导致失去连接时可用
	 *
	 * @return $this
	 */
	public function resetDbPool()
	{
		$this->_dbPool = array();
		return $this;
	}
	
	/**
	 * 关闭连接
	 * @return $this
	 */
	public function close()
	{
		if ( $this->_dbPool ) {
			$this->_dbPool = null;
		}
		return $this;
	}
	
	/**
	 * Pdo SQL快速生成辅助工具
	 * @return \Pol\Db\unknown
	 */
	public function assist()
	{
		if ( null === $this->_assist ) {
			$this->_assist = new Assist();
		}
		
		return $this->_assist;
	}

}