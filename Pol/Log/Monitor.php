<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 框架监控上报类：                                                        |
 * |    用于类库调用过程产生的错误进行配置和上报                                  |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Log;

class Monitor
{

	/**
	 * 上报host
	 * @var string
	 */
	protected $_host = '';

	/**
	 * 上报端口
	 * @var int
	 */
	protected $_port = 0;
	
	/**
	 * 是否上报
	 * @var string
	 */
	protected $_bolReport = false;

	/**
	 * Monitor instance
	 * @var obj
	 */
	protected static $_arrInstance = array();

	/**
	 * @brief 获取日志实例
	 * @param string $ip
	 * @param int $port
	 * @return
	 */
	public static function getInstance($ip, $port)
	{
		$key = implode(':', array($ip, $port));

		if ( !isset(self::$_arrInstance[$key]) ) {
			self::$_arrInstance[$key] = new self($ip, $port);
		}
		return self::$_arrInstance[$key];
	}

	/**
	 * @brief 初始化监控上报类
	 * @param string $ip
	 * @param int $port
	 */
	protected function __construct($host, $port)
	{
		$this->_host    = $host;
		$this->_port    = $port;
	}
	
	/**
	 * 设置是否进行上报的开关
	 * @param unknown $bolReport
	 * @return \Pol\Log\Monitor
	 */
	public function setReport($bolReport)
	{
		$this->_bolReport = $bolReport;
		return $this;
	}

	/**
	 * 上报监控报警的数量
	 * @param int $mid 参数上报id 在监控报警系统中定义
	 * @param int $num 上报数量
	 */
	public function reportCount($mid, $num = 1)
	{
		if ( false === $this->_bolReport ) {
			return;
		}
		
		$seq = Logger::genLogId();
		$message = $seq . '-==-' . date("Y-m-d H:i:s") . '-==-' . $mid . '-==-' . $num;

		if (is_numeric($mid)) {
			$this->reportUdp($message);
		}
	}

	/**
	 * 上报延迟
	 * @param int $mid 参数上报id 在监控报警系统中定义
	 * @param int $time 上报时间(ms)
	 */
	public function reportTime($mid, $time)
	{
		if ( false === $this->_bolReport ) {
			return;
		}
		
		$seq = Logger::genLogId();
		$message = $seq . '-==-' . date("Y-m-d H:i:s") . '-==-' . $mid . '-==-' . $time;

		if (is_numeric($mid)) {
			$this->reportUdp($message);
		}
	}

	/**
	 * 上报内存使用
	 * @param int $mid 参数上报id 在监控报警系统中定义
	 * @param int $byte 内存的使用 字节
	 */
	public function reportMem($mid, $byte)
	{
		if ( false === $this->_bolReport ) {
			return;
		}
		
		$seq = Logger::genLogId();
		$message = $seq . '-==-' . date("Y-m-d H:i:s") . '-==-' . $mid . '-==-' . $byte;

		if (is_numeric($mid)) {
			$this->reportUdp($message);
		}
	}

	/**
	 * 汇报到监控系统
	 * @param string $message
	 */
	public function reportUdp($message)
	{
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
		$len = strlen($message);
		socket_sendto($socket, $message, $len, 0, $this->_host, $this->_port);
		socket_close($socket);
	}

}