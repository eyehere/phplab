<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | NoSQL之Memcached类：                                             	  |
 * |      Memcached使用类					                                  |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Ns;

class Memcached
{
	
	//最长执行时间
	CONST MAX_EXEC_TIME = 300;

	/**
	 * memcached连接句柄
	 * @var unknown
	 */
	protected $_memh = null;

	/**
	 * 执行的结果：code message method args ret
	 * @var array
	 */
	protected $_result = array();

	/**
	 * memcached配置
	 * @var array
	 */
	protected $_conf = array();

	/**
	 * Memcached的选项配置 http://php.net/manual/zh/memcached.constants.php
	 * @var array
	 */
	private static $opts = array(
			//指定元素key分布到各个服务器的方法,一致性分布算法(基于libketama)
			\Memcached::OPT_DISTRIBUTION 			=> \Memcached::DISTRIBUTION_CONSISTENT,
			//当开启此选项后，元素key的hash算法将会被设置为md5并且分布算法将会 采用带有权重的一致性hash分布
			\Memcached::OPT_LIBKETAMA_COMPATIBLE 	=> true,
			//开启或关闭压缩功能
			\Memcached::OPT_COMPRESSION 			=> true,
			\Memcached::OPT_TCP_NODELAY 			=> true,
			\Memcached::OPT_CONNECT_TIMEOUT 		=> 500,
			\Memcached::OPT_SEND_TIMEOUT 			=> 1000,
			\Memcached::OPT_RECV_TIMEOUT 			=> 1000,
			//等待失败的连接重试的时间，单位秒
			\Memcached::OPT_RETRY_TIMEOUT 			=> 1,
			//开启或关闭异步I/O
			//\Memcached::OPT_NO_BLOCK 				=> true,
			//开启使用二进制协议。请注意这个选项不能在一个打开的连接上进行切换
			//\Memcached::OPT_BINARY_PROTOCOL 		=> true,
				
			//指定对于非标量值进行序列化的序列化工具,JSON序列化 php warning invalid serializer provided
			//\Memcached::OPT_SERIALIZER 			=> \Memcached::SERIALIZER_JSON,
			//指定存储元素key使用的hash算法
			//\Memcached::OPT_HASH 					=> \Memcached::HASH_MD5,
	);

	/**
	 * 
	 * @var array
	 */
	private static $badKey = array(
			\Memcached::RES_BAD_KEY_PROVIDED,					//提供了无效的key
			\Memcached::RES_STORED,
			\Memcached::RES_DELETED,
			\Memcached::RES_STAT,
			\Memcached::RES_ITEM,
			\Memcached::RES_NOT_SUPPORTED,
			\Memcached::RES_FETCH_NOTFINISHED,
			\Memcached::RES_SERVER_MARKED_DEAD,
			\Memcached::RES_UNKNOWN_STAT_KEY,
			\Memcached::RES_INVALID_HOST_PROTOCOL,
			\Memcached::RES_MEMORY_ALLOCATION_FAILURE,
			\Memcached::RES_E2BIG,
			\Memcached::RES_KEY_TOO_BIG,
			\Memcached::RES_SERVER_TEMPORARILY_DISABLED,
			\Memcached::RES_SERVER_MEMORY_ALLOCATION_FAILURE,
			//\Memcached::RES_AUTH_PROBLEM,
			//\Memcached::RES_AUTH_FAILURE,
			//\Memcached::RES_AUTH_CONTINUE,
	);
	
	/**
	 * Memcached返回结果
	 * @var array
	 */
	private static $failureRes = array(
			\Memcached::RES_FAILURE,				//某种方式的操作失败。
			\Memcached::RES_HOST_LOOKUP_FAILURE,	//DNS查找失败
			\Memcached::RES_UNKNOWN_READ_FAILURE,	//读取网络数据失败
			\Memcached::RES_PROTOCOL_ERROR,			//错误的memcached协议命令
			\Memcached::RES_CLIENT_ERROR,			//客户端错误
			\Memcached::RES_SERVER_ERROR,			//服务端错误
			\Memcached::RES_WRITE_FAILURE,			//向网络写数据失败
			//比较并交换值操作失败（cas方法）：尝试向服务端存储数据时由于自此连接最后一次取此key对应数据之后被改变导致失败
			\Memcached::RES_DATA_EXISTS,
			//元素没有被存储，但并不是因为一个错误。
			//这通常表明add（元素已存在）或replace（元素不存在）方式存储数据失败或者元素已经在一个删除序列中（延时删除）
			\Memcached::RES_NOTSTORED,
			\Memcached::RES_NOTFOUND,				//元素未找到（通过get或cas操作时）
			\Memcached::RES_PARTIAL_READ,			//获取网络数据读错误
			\Memcached::RES_SOME_ERRORS,			//在多key获取的时候发生错误
			\Memcached::RES_NO_SERVERS,				//服务器池空
			\Memcached::RES_END,					//结果集到结尾了
			\Memcached::RES_ERRNO,					//系统错误
			\Memcached::RES_BUFFERED,				//操作被缓存
			\Memcached::RES_TIMEOUT,				//操作超时
			//创建网络socket失败
			\Memcached::RES_CONNECTION_SOCKET_CREATE_FAILURE,
			\Memcached::RES_PAYLOAD_FAILURE,		//不能压缩/解压缩或序列化/反序列化值
	);

	/**
	 * 初始化Memcached
	 * @param unknown $config
	 * @throws \Pol\Exception\McFailed
	 */
	public function __construct($config)
	{
		if ( !class_exists('\Memcached') ) {
			throw new \Pol\Exception\McFailed("Class Memcached not exists, please install the php Memcached extension...");
		}
		if ( empty($config) ) {
			throw new \Pol\Exception\McFailed("memcached config is null");
		}
		$this->_conf = $config;

		$id = isset($this->_conf['persistent_id']) ? $this->_conf['persistent_id'] : 'default';
		$this->_memh = new \Memcached($id);
		$this->_memh->setOptions(self::$opts);
	}

	/**
	 * 设置选项信息
	 * @param array $opts
	 */
	public static function setOption(array $opts = array())
	{
		if( $opts ) {
			foreach( $opts as $k => $v ) {
				self::$opts[$k] = $v;
			}
		}
	}

	/**
	 * 获取连接
	 *
	 * @return \Memcached
	 * @throws \Pol\Exception\McFailed
	 */
	private function _getConn()
	{
		//创建memcached对象
		$startTime  = microtime(true);
		$serverList = $this->_memh->getServerList();
		if ( empty(count($serverList)) ) {
			$servers 	= array();
			$hosts 		= explode(',',$this->_conf['host']);
			$ports 		= explode(',', $this->_conf['port']);
			$weights	= isset($this->_conf['weight']) ? 
							explode(',', $this->_conf['weight']) : array();
			foreach ( $hosts as $key=>$host ) {
				$port 		= isset($ports[$key]) ? $ports[$key] : $ports[0];
				$weight 	= isset($weights[$key]) ? $weights[$key] : 1;
				$servers[] 	= array('host'=>$host,'port'=>$port,'weight'=>$weight);
			}

			$ret = $this->_memh->addServers($servers);

			$endTime = microtime(true);
			$runTime = 1000 * floatval($endTime - $startTime);
			$runTime = sprintf('%.3f',$runTime);
			if ( $runTime > 100 ) {
				$log = array('tip'=>'memcachedAddServers time is too long','config'=>$servers,'runTime'=>$runTime);
				\Pol\Log\PolLog::McLog()->warning($log);
			}

			if ( $ret === false ) {
				$log = array('tip'=>'memcachedAddServersError','config'=>json_encode($this->_conf));
				\Pol\Log\PolLog::McLog()->error($log);
				\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::MC_ADD_SERVERS_EXCEPTION);
				throw new \Pol\Exception\McFailed('memcacheAddServersError,config:'.json_encode($this->_conf));
			}
		}
		return $this->_memh;
	}

	/**
	 * 关闭当前池子里的连接
	 * @return bool
	 */
	public function close()
	{
		return $this->_memh && $this->_memh->resetServerList();
	}
	
	/**
	 *  禁止克隆
	 */
	private function __clone()
	{

	}

	/**
	 * @param $method
	 * @param $args
	 * @return bool|mixed
	 * @throws \Pol\Exception\McFailed
	 */
	public function __call($method, $args)
	{
		$link = $this->_getConn();
		if ( !($link instanceof \Memcached) || !method_exists($link, $method) ) {
			throw new \Pol\Exception\McFailed("Class Memcached not have method ($method) ");
		}
		$startTime = microtime(true);
		try {
			$ret = call_user_func_array(array($link, $method), $args);
		} catch ( \Exception $e ) {
			$log = array('tip'=>'memcachedCallException','method'=>$method,'params'=>$args,'code'=>$e->getCode(),'message'=>$e->getMessage());
			\Pol\Log\PolLog::McLog()->error($log);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::MC_CALL_EXCEPTION);
			throw $e;
		}
		$code = $this->_memh->getResultCode();
		$endTime = microtime(true);
		$runTime = 1000 * floatval($endTime - $startTime);
		$runTime = sprintf('%.3f',$runTime);
		if ( bccomp($runTime,self::MAX_EXEC_TIME,2) == 1 ) {
			$log = array('tip'=>'memcached call time is too long','method'=>$method,'params'=>$args,'runTime'=>$runTime);
			\Pol\Log\PolLog::McLog()->warning($log);
		}
		if ( in_array($code,self::$badKey) ) {
			$message = $this->_memh->getResultMessage();
			$this->_result['code'] 		= $code;
			$this->_result['message'] 	= $message;
			$this->_result['method'] 	= $method;
			$this->_result['args'] 		= $args;
			$this->_result['ret'] 		= $ret;
			
			$log = $this->_result;
			$log['tip'] = 'memcachedResultBadKey';
			\Pol\Log\PolLog::McLog()->warning($log);
		}
		return $ret;
	}

	/**
	 * 获取调用结果(错误码及消息)
	 * @return array
	 */
	public function getResult(){
		return $this->_result;
	}
	
}