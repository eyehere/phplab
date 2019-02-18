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
	
	/**
	 * 监控对照表
	 * @var array
	 */
	protected static $_monitorMap = array(
			self::DB_CONNECT_EXCEPTION	=> false,
			self::DB_METHOD_UNKNOWN		=> false,
			self::DB_STATEMENT_UNKMOWN 	=> false,
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