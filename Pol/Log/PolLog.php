<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 框架日志类：                                                           |
 * |    用于配置和记录框架本身产生的日志                           |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Log;

use \Pol\Manager\Di;

class PolLog
{
	/**
	 * Pol日志的基础目录
	 * @var unknown
	 */
	protected static $_polLogDir = '/tmp/';
	
	/**
	 * 配置日志的后缀，主要用于根据日志量按天/小时等级别对日志进行分割
	 * @var unknown
	 */
	protected static $_suffix = null;
	
	/**
	 * 日志级别
	 * @var unknown
	 */
	protected static $_logLevel = null;
	
	/**
	 * 是否开启web跟踪
	 * @var string
	 */
	protected static $_bolWebTrace = false;
	
	/**
	 * 是否开启debug跟踪
	 * @var string
	 */
	protected static $_bolBackTrace = false;
	
	/**
	 * 是否初始化过pol的配置
	 * @var string
	 */
	protected static $_bolInited = false;

	/**
	 * 从应用层获取应用层用户对Pol日志需求的配置
	 * @return boolean
	 */
	protected static function _init()
	{
		if ( self::$_bolInited ) {
			return true;
		}
		self::$_bolInited = true;
		
		$arrConf = Di::get('PolConf');
		if ( empty($arrConf) || empty($arrConf['logConf']) ) {
			return false;
		}
		
		$logConf = $arrConf['logConf'];
		if ( empty($logConf['dir']) ) {
			return false;
		}
		
		self::$_polLogDir 	= rtrim($logConf['dir'], array('/', '\\')) . DIRECTORY_SEPARATOR;
		self::$_suffix		= isset($logConf['suffix']) ? $logConf['suffix'] : date('Y-m-d-H');
		self::$_logLevel	= isset($logConf['level']) ? $logConf['level'] : Logger::LOG_OFF;
		self::$_bolWebTrace	= isset($logConf['webTrace']) ? $logConf['webTrace'] : false;
		self::$_bolBackTrace= isset($logConf['backTrace']) ? $logConf['backTrace'] : false;
		
		return true;
	}
	
	/**
	 * 通用Pol不同日志类别 如： dbLog() RedisLog()
	 * @return mixed
	 */
	public static function __callstatic($method, $arguments)
	{
		if ( false === self::_init() ) {
			self::$_logLevel = Logger::LOG_OFF;
		}
		
		$logName = strtoupper(substr($method, 0, -3));
		$logFile	= self::$_polLogDir . $logName . '-' . self::$_suffix;
		$objDbLog 	= Logger::getLoggerInstance($logFile, self::$_logLevel, 
									self::$_bolWebTrace, self::$_bolBackTrace);
		return $objDbLog;
	}

}