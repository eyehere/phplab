<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | NoSQL之Redis类：                                 	            	  |
 * |      Redis使用类	(高可用层面配合DNS+集群化管理思路)				          |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Ns;

class Redis
{
	
	const MASTER 	= 'master';
	const SLAVE 	= 'slave';
	
	//监控的redis连接最长时间(ms)
	const MAX_CONNECT_TIME = 100;
	//监控的最大执行时间(ms)
	const MAX_EXEC_TIME	= 300;
	//最大重试次数
	const RETRY_MAX_TIMES = 1;
	//再次重试的最大时间间隔
	const RETRY_INTERVAL = 2;
	
	/**
	 * redis配置
	 * @var array
	 */
	protected $_conf = array();
	
	/**
	 * redis连接池(主要配合主从)
	 * @var array
	 */
	protected $_pool = array();
	
	/**
	 * 标识是否建立长连接
	 * @var string
	 */
	protected $_bolPConnect = false;
	
	/**
	 * 是否强制使用主库
	 * @var string
	 */
	protected $_forceMaster = false;

	/**
	 * redis写操作命令
	 * @var array
	 */
	protected $_writeCmds = array(
			'append',
			'bitop',
			'blpop',
			'brpop',
			'brpoplpush',
			'decr',
			'decrby',
			'delete',
			'discard',
			'eval',
			'exec',
			'expireat',
			'geoadd',
			'hdel',
			'hincrby',
			'hincrbyfloat',
			'hmset',
			'hset',
			'hsetnx',
			'incr',
			'incrby',
			'incrbyfloat',
			'linsert',
			'lpop',
			'lpush',
			'lpushx',
			'lremove',
			'lset',
			'move',
			'mset',
			'msetnx',
			'multi',
			'pexpire',
			'pexpireat',
			'psetex',
			'publish',
			'pubsub',
			'punsubscribe',
			'rpop',
			'rpush',
			'rpushx',
			'renamekey',
			'renamenx',
			'restore',
			'rpoplpush',
			'sadd',
			'saddarray',
			'sdiffstore',
			'sinter',
			'sinterstore',
			'smove',
			'spop',
			'sremove',
			'sunionstore',
			'save',
			'script',
			'set',
			'setbit',
			'setrange',
			'setex',
			'setnx',
			'unlink',
			'unsubscribe',
			'unwatch',
			'watch',
			'zadd',
			'zdelete',
			'zdeleterangebyrank',
			'zdeleterangebyscore',
			'zincrby',
			'zinter',
			'zunion',
			'del',
			'expire',
			'lrem',
			'ltrim',
			'rename',
			'scard',
			'srem',
			'substr',
			'zrem',
			'zremrangebyrank',
			'zremrangebyscore',
			'zremove',
			'zremoverangebyscore',
			'zreverserange',
			'zinterstore',
			'zunionstore',
	);

	/**
	 * redis配置等各方面初始化
	 * @param unknown $config
	 */
	public function __construct($config)
	{
		$this->_conf 		= array(
				'master'	=> $this->_getStandConf($config['master']),
				'slave'		=> $this->_getStandConf($config['slave'])
		);
	}
	
	/**
	 * 生成规范化的配置
	 * @param unknown $config
	 * @throws \Pol\Exception\RedisFailed
	 * @return number[]|unknown[]
	 */
	protected function _getStandConf($config)
	{
		if ( empty($config['host']) ) {
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_CONF_EXCEPTION);
			throw new \Pol\Exception\RedisFailed('RedisConfigInvalid,host id needed. config:' . json_encode($config));
		}
		
		$arrConf = array(
					'host'		=> $config['host'],
					'port'		=> isset($config['port']) ? $config['port'] : 6379,
					'timeout'	=> isset($config['timeout']) ? $config['timeout'] : 1, 
					'retry'		=> 0, 
					'interval'	=> 0
			);
	
		return $arrConf;
	}

	/**
	 * 获取redis连接
	 * @param unknown $config
	 * @param string $hasRetryChance
	 * @throws \Pol\Exception\RedisFailed
	 * @throws Exception
	 * @return \Redis|boolean
	 */
	protected function _getRedis($config, $hasRetryChance = false)
	{
		try {
			$startTime 			= microtime(true);
			
			$redis 				= new \Redis();
			
			$config['timeout'] 	= isset($config['timeout']) ? $config['timeout'] : 1;
			$this->_bolPConnect = isset($this->_conf['pConnect']) ? $this->_conf['pConnect'] : false;
			
			$ret = false;
			if ( $this->_bolPConnect ) {
				$ret = $redis->pconnect($config['host'],$config['port'],$config['timeout']);
			} else {
				$ret = $redis->connect($config['host'],$config['port'],$config['timeout']);
			}
			$endTime = microtime(true);
			$runTime = 1000 * floatval($endTime - $startTime);
			$runTime = sprintf('%.3f',$runTime);
			if ( $runTime > self::MAX_CONNECT_TIME ) {
				$log = array('tip'=>'redisConnect time is too long','config'=>$config,'runTime'=>$runTime);
				\Pol\Log\PolLog::RedisLog()->warning($log);
			}
			if ( $ret === false ) {
				$log = array('tip'=>'redisConnectException','config'=>$config);
				\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_CONNECT_EXCEPTION);
				\Pol\Log\PolLog::RedisLog()->error($log);
				throw new \Pol\Exception\RedisFailed('redisConnectFailed. config:'.json_encode($config));
			}
			return $redis;
			
		} catch ( \Exception $e ) {
			$log = array('tip'=>'redisConnectException','config'=>$config,'code'=>$e->getCode(), 'message'=>$e->getMessage());
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_CONNECT_EXCEPTION);
			\Pol\Log\PolLog::RedisLog()->error($log);
			if ( false === $hasRetryChance ) {
				throw $e;
			}
			return false;
		}
	}

	/**
	 * 建立连接/连接池 或者 从连接池获取连接
	 * @param unknown $type
	 * @param string $isConnect
	 * @throws \Pol\Exception\RedisFailed
	 * @return boolean|mixed
	 */
	protected function _getConn($type = self::SLAVE, $reConnect = false)
	{
		$objRedis = !empty($this->_pool[$type]) && !$reConnect ? 
					$this->_pool[$type] : null;
		
		if ( null !== $objRedis && $this->_bolPConnect ) {
			try {
				$objRedis->ping();
			} catch ( \Exception $e ) {
				$log = array(
						'tip'		=>	'redis long connect lose',
						'code'		=>	$e->getCode(), 
						'message'	=>	$e->getMessage(),
						'config'	=>	json_encode($this->_conf[$type])
				);
				\Pol\Log\PolLog::RedisLog()->warning($log);
				
				$objRedis = $this->_getConn($type, $reConnect);
			}
		}
		
		if ( $objRedis ) {
			return $objRedis;
		}
		
		if ( time()-$this->_conf[$type]['interval'] > self::RETRY_INTERVAL ) {
			$this->_conf[$type]['retry'] = 0;
		}
		
		while ( time()-$this->_conf[$type]['interval'] <= self::RETRY_INTERVAL && 
			 $this->_conf[$type]['retry']++ < self::RETRY_MAX_TIMES ) {
			 
			 $this->_conf[$type]['interval'] = time();
			 
			 $objRedis = $this->_getRedis($config, $this->_conf[$type]['retry'] < self::RETRY_MAX_TIMES);
			 
			 if ( $objRedis ) {
			 	$this->_pool[$type] = $objRedis;
			 	break;
			 }
		}
		
		if( !$objRedis ) {
			$log = array('tip'=>'redisConnectFailed', 'type'=>$type,'config'=>json_encode($this->_conf[$type]));
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_CONNECT_EXCEPTION);
			\Pol\Log\PolLog::RedisLog()->error($log);
			throw new \Pol\Exception\RedisFailed('redisConnectFailed. config:' . json_encode($this->_conf['type']));
		}
		
		return $objRedis;
	}

	/**
	 * redis方法调用入口
	 * @param unknown $method
	 * @param array $params
	 * @throws \Pol\Exception\RedisFailed
	 * @return mixed
	 */
	public function __call($method, $params = array())
	{
		$type = self::SLAVE;
		//写操作自动连接master
		$cmd = strtolower($method);
		if ( $this->_forceMaster || in_array($cmd, $this->_writeCmds) ) {
			$type = self::MASTER;
		}
		
		$objRedis = $this->_getConn($type);

		if ( !method_exists($objRedis, $method) ) {
			$log = array('tip'=>'redisMethodNotExists','method'=>$method,'params'=>json_encode($params));
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_METHOD_NOT_EXIST);
			\Pol\Log\PolLog::RedisLog()->error($log);
			throw new \Pol\Exception\RedisFailed('redisMethodNotExists,method:'.$method.',params:'.json_encode($params));
		}
		
		$startTime = microtime(true);
		
		try {
			$result = call_user_func_array(array($objRedis,$method), $params);
		} catch ( \Exception $e ) {
			$log = array(
					'tip'		=>	'redis call exception',
					'code'		=>	$e->getCode(),
					'message'	=>	$e->getMessage(),
					'config'	=>	json_encode($this->_conf[$type])
			);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::REDIS_CALL_EXCEPTION);
			\Pol\Log\PolLog::RedisLog()->error($log);
			throw $e;
		}
		
		$endTime = microtime(true);
		$runTime = 1000 * floatval($endTime - $startTime);
		$runTime = sprintf('%.3f',$runTime);
		
		if ( bccomp($runTime,self::MAX_TIME,2) == 1 ) {
			$log = array('tip'=>'redis call time is too long','method'=>$method,'params'=>$params,'runTime'=>$runTime);
			\Pol\Log\PolLog::RedisLog()->warning($log);
		}
		
		return $result;
	}
	
	/**
	 * 强制主
	 * @return $this
	 */
	public function forceMaster()
	{
		$this->_forceMaster = true;
		return $this;
	}
	
	/**
	 * 强制从
	 * @return $this
	 */
	public function forceSlave()
	{
		$this->_forceMaster = false;
		return $this;
	}
	
	/**
	 * 是否使用长连接
	 * @param unknown $bolPconnect
	 */
	public function setPconnect($bolPconnect)
	{
		$this->_bolPConnect = $bolPconnect;
	}
	
	/**
	 * 禁止clone对象
	 */
	private function __clone()
	{
	
	}
	
}