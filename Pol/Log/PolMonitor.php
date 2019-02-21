<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 监控上报类：                                                           |
 * |    用于监控上报在Pol调用过程中产生的错误                                    |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Log;

class PolMonitor
{
	//db连接错误
	const DB_CONNECT_EXCEPTION	= 'DB_CONNECT_EXCEPTION';
	
	//db调用方法不存在
	const DB_METHOD_UNKNOWN 	= 'DB_METHOD_UNKNOWN';
	
	//statement调用方法不存在
	const DB_STATEMENT_UNKMOWN 	= 'DB_STATEMENT_UNKNOWN';
	
	//SQL执行异常
	const DB_EXEC_EXCEPTION		= 'DB_EXEC_EXCEPTION';
	
	//MC add servers exception
	const MC_ADD_SERVERS_EXCEPTION = 'MC_ADD_SERVERS_EXCEPTION';
	
	//MC调用异常
	const MC_CALL_EXCEPTION		= 'MC_CALL_EXCEPTION';
	
	//redis配置异常
	const REDIS_CONF_EXCEPTION = 'REDIS_CONF_EXCEPTION';
	
	//redis方法没找到
	const REDIS_METHOD_NOT_EXIST = 'REDIS_METHOD_NOT_EXIST';
	
	//redis连接异常
	const REDIS_CONNECT_EXCEPTION = 'REDIS_CONNECT_EXCEPTION';
	
	//redis调用失败
	const REDIS_CALL_EXCEPTION = 'REDIS_CALL_EXCEPTION';
	
	//http调用失败
	const HTTP_CALL_EXCEPTION = 'HTTP_CALL_EXCEPTION';
	
	/**
	 * 监控对照表
	 * @var array
	 */
	protected static $_monitorMap = array(
			self::DB_CONNECT_EXCEPTION	=> false,
			self::DB_METHOD_UNKNOWN		=> false,
			self::DB_STATEMENT_UNKMOWN 	=> false,
			self::MC_ADD_SERVERS_EXCEPTION	=> false,
			self::MC_CALL_EXCEPTION		=> false,
			self::REDIS_CONF_EXCEPTION	=> false,
			self::REDIS_METHOD_NOT_EXIST=> false,
			self::REDIS_CONNECT_EXCEPTION=> false,
			self::REDIS_CALL_EXCEPTION	=> false,
			self::HTTP_CALL_EXCEPTION	=> false,
	);
	
	/**
	 * 上报配置
	 * @var string
	 */
	protected static $_conf = array();
	
	/**
	 * 上报Host
	 * @var string
	 */
	protected static $_reportHost = '';

	/**
	 * 上报端口
	 * @var integer
	 */
	protected static $_reportPort = 0;

	/**
	 * 是否初始化过pol的配置
	 * @var string
	 */
	protected static $_bolInited = false;

	/**
	 * 从应用层获取应用层用户对Pol监控上报需求的配置
	 * @return boolean
	 */
	protected static function _init()
	{
		if ( self::$_bolInited ) {
			return true;
		}
		self::$_bolInited = true;

		$arrConf = Di::get('PolConf');
		if ( empty($arrConf) || empty($arrConf['monitorConf']) ) {
			return false;
		}

		$monitorConf = $arrConf['monitorConf'];
		if ( empty($monitorConf['host']) ) {
			return false;
		}

		self::$_reportHost	= isset($monitorConf['host']) ? $monitorConf['host'] : '';
		self::$_reportPort	= isset($monitorConf['port']) ? $monitorConf['port'] : 0;
		self::$_conf		= $monitorConf;

		return true;
	}

	/**
	 * Pol上报通用入口
	 * @return mixed
	 */
	public static function __callstatic($method, $arguments)
	{
		if ( false === self::_init() ) {
			self::$_bolReport = false;
		}
		
		$errorType 	= isset($arguments[0]) ? $arguments[0] : null;
		$reportId	= false;
		if ( null !== $errorType && isset(self::$_monitorMap[$errorType]) && isset(self::$_conf[$errorType]) ) {
			$reportId = self::$_conf[$errorType];
		}
		
		$reportCon = isset($arguments[1]) ? $arguments[1] : 1;
		
		$bolReport = self::$_bolReport && is_numeric($reportId); 
		
		$objMonitor = Monitor::getInstance(self::$_reportHost, self::$_reportPort);
		$objMonitor->setReport($bolReport);
		
		return call_user_func_array(array($objMonitor, $method), array($reportId, $reportCon));
	}
	
}