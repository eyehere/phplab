<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 通用日志类：                                                           |
 * |    可基于这个类定制自己的日志文件格式及日志内容格式                           |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Log;

class Logger
{

	/**
	 * 多个logger的instance
	 * @var array
	 */
	protected static $arrLoggers = array();
	
	/**
	 * 一次请求里多条日志的一个唯一的ID
	 * @var integer
	 */
	protected static $_logId = 0;

	//日志级别
	const DEBUG     =   0X00;
	const INFO      =   0X02;
	const NOTICE    =   0X04;
	const WARNING   =   0X08;
	const ERROR     =   0X10;
	const CRITICAL  =   0X20;
	const ALERT     =   0X40;
	const EMERGENCY =   0X80;
	
	//日志关闭
	const LOG_OFF = 0X100;

	/**
	 * 日志级别中文对应
	 * @var array
	 */
	protected static $arrLevels = array(
			0X00    =>  'DEBUG',
			0X02    =>  'INFO',
			0X04    =>  'NOTICE',
			0X08    =>  'WARNING',
			0X10    =>  'ERROR',
			0X20    =>  'CRITICAL',
			0X40    =>  'ALERT',
			0X80    =>  'EMERGENCY'
	);

	//开启web跟踪
	const WEB_TRACE_ON  =   true;
	//开启debug跟踪
	const BACK_TRACE_ON =   true;

	//日志文件
	protected $_strLogFile = null;

	//日志级别
	protected $_logLevel  =   null;

	//记录web相关的信息
	protected $_bolWebTrace   =   false;

	//记录debug信息
	protected $_bolBackTrace  =   false;

	//是否打印日志的大类分隔标识
	protected $_bolShowMark = true;
	
	//日志分隔符
	protected $_strSeparator = '-==-';

	/**
	 * 创建唯一的序列化字段logId,主要为了查出一次请求中的所有log
	 */
	public static function genLogId()
	{
		if ( !self::$_logId ) {
			$str = ((mt_rand() << 1) | (mt_rand() & 1) ^ intval(microtime(true)));
			$logId = strtoupper(base_convert($str, 10, 36));
			//补齐六位
			self::$_logId = str_pad($logId, 6, 'X');
		}
		return self::$_logId;
	}
	
	/**
	 * 日志实例化
	 *
	 * @param $name 标识
	 * @param $logFile 日志全路径文件
	 * @param int $logLevel
	 * @param bool|false $webTrace
	 * @param bool|false $backTrace
	 */
	protected function __construct($logFile, $logLevel = self::DEBUG,	$webTrace = false,	$backTrace = false)
	{
		if ( empty($logFile) ) {
			throw new \Pol\Exception\LoggerFailed("Log {$logFile} file name is not allowed null");
		}
		
		$logPath = dirname($logFile);
		if ( ! is_dir($logPath)) {
			mkdir($logPath, 0755, true);
		}
		$this->_strLogFile      = $logFile;
		$this->_logLevel        = $logLevel;
		$this->_bolWebTrace     = $webTrace;
		$this->_bolBackTrace    = $backTrace;

		self::genLogId();
	}
	
	/**
	 * 设置大类的分隔符是否打印
	 * @param unknown $bolShow
	 * @return \Pol\Log\Logger
	 */
	public function setShowMark($bolShow)
	{
		$this->_bolShowMark = $bolShow;
		return $this;
	}
	
	/**
	 * 设置日志内容的分隔符 -==- ||
	 * @param unknown $separator
	 * @return \Pol\Log\Logger
	 */
	public function setSeparator($separator)
	{
		$this->_strSeparator = $separator;
		return $this;
	}

	/**
	 * 日志格式化
	 * @param array $arrLog
	 * @return string
	 */
	protected function _lineFormat($arrLog)
	{
		$line = '';
		foreach ($arrLog as $mark=>$log) {

			if ( $this->_bolShowMark ) {
				$line .= empty($line) ? "[$mark]" : "||[$mark]";
			}
			
			if ( is_array($log) ) {
				foreach ( $log as $key=>$val ) {
					if ( !is_scalar($val) ) {
						$val = json_encode($val, JSON_UNESCAPED_UNICODE);
					}
					$line .= strval($key) . '=' . strval($val) . $this->_strSeparator;
				}
			} elseif (is_string($log) ) {
				$line .= $log;
			} else {
				$line .= json_encode($log, JSON_UNESCAPED_UNICODE);
			}
		}
		return $line . PHP_EOL;
	}

	/**
	 * 写日志
	 * @param int $level
	 * @param array $arrLog
	 * @return boolean
	 */
	protected function _writeLog($level, $arrLog)
	{
		if ( $level < $this->_logLevel ) {
			return true;
		}
		$arrPrepend = array(
				'write_time'    =>  date('Y-m-d H:i:s'),
				'log_id'        =>  self::$_logId
		);

		if ( !is_array($arrLog[self::$arrLevels[$level]]) ) {
			$arrLog[self::$arrLevels[$level]] = array($arrLog[self::$arrLevels[$level]]);
		}

		$arrLog[self::$arrLevels[$level]] = array_merge($arrPrepend, $arrLog[self::$arrLevels[$level]]);

		if ( $this->_bolWebTrace ) {
			$arrLog['WEB_TRACE'] = $this->_getWebTrace();
		}

		if ( $this->_bolBackTrace ) {
			$arrLog['BACK_TRACE'] = self::_getBackTrace();
		}

		$logContent = $this->_lineFormat($arrLog);
		return $this->_write($level, $logContent);
	}

	/**
	 * web访问信息跟踪
	 * @return array
	 */
	protected function _getWebTrace()
	{
		return array(
				'http_host'	=> 	isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
				'uri'   	=>  isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
				'query' 	=>  isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '',
				'refer' 	=>  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
				'user_agent'=>	isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
				'clint_ip'	=>  isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
				'post_data'	=>	json_encode($_POST,JSON_UNESCAPED_UNICODE)
		);
	}

	/**
	 * @param int $index
	 * @brief debug 跟踪信息
	 * @return array
	 */
	protected static function _getBackTrace($index = 4)
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace = array_slice($trace,$index);
		$trace[1] = empty($trace[0]) ? array() : $trace[0];
		return array(
				'file'      =>  isset($trace[0]) && isset($trace[0]['file']) ? $trace[0]['file'] : null,
				'line'      =>  isset($trace[0]) && isset($trace[0]['line']) ? $trace[0]['line'] : null,
				'class'     =>  isset($trace[1]) && isset($trace[1]['class']) ? $trace[1]['class'] : null,
				'func'      =>  isset($trace[1]) && isset($trace[1]['function']) ? $trace[1]['function'] : null,
		);
	}

	/**
	 * debug log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function debug($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::DEBUG]   => $arrLog
		);
		return $this->_writeLog(self::DEBUG, $arrMessage);
	}

	/**
	 * info log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function info($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::INFO]    => $arrLog
		);
		return $this->_writeLog(self::INFO, $arrMessage);
	}

	/**
	 * notice log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function notice($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::NOTICE]  => $arrLog
		);
		return $this->_writeLog(self::NOTICE, $arrMessage);
	}

	/**
	 * warning log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function warning($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::WARNING] => $arrLog
		);
		return $this->_writeLog(self::WARNING, $arrMessage);
	}

	/**
	 * error log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function error($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::ERROR]   => $arrLog
		);
		return $this->_writeLog(self::ERROR, $arrMessage);
	}

	/**
	 * critical log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function critical($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::CRITICAL]    => $arrLog
		);
		return $this->_writeLog(self::CRITICAL, $arrMessage);
	}

	/**
	 * alert log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function alert($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::ALERT]   => $arrLog
		);
		return $this->_writeLog(self::ALERT, $arrMessage);
	}

	/**
	 * emergency log
	 * @param array $arrLog
	 * @return boolean
	 */
	public function emergency($arrLog)
	{
		$arrMessage = array(
				self::$arrLevels[self::EMERGENCY]   => $arrLog
		);
		return $this->_writeLog(self::EMERGENCY, $arrMessage);
	}

	/**
	 * @brief 写日志
	 * @param string $logContent
	 * @return boolean
	 * @throws \Exception
	 */
	protected function _write($level, $logContent)
	{
		$logDir 	= dirname($this->_strLogFile) . DIRECTORY_SEPARATOR;
		$logFile 	= basename($this->_strLogFile);
		$logFileName = $logDir . strtoupper(self::$arrLevels[$level]) . '_' . $logFile;
		$objLog = fopen($logFileName,'a');
		if ( false === $objLog || !is_resource($objLog) ) {
			throw new \Pol\Exception\LoggerFailed("log file {$logFileName} open failed!");
		}
		$logContent = str_replace(array("\r", "\n", "\r\n"), ' ', $logContent) . PHP_EOL;
		if ( false === fwrite($objLog, $logContent) ) {
			throw new \Pol\Exception\LoggerFailed("log file {$logFileName} write failed!");
		}
		fclose($objLog);
		return true;
	}

	/**
	 * 获取log实例
	 *
	 * @param $name
	 * @param $logFile
	 * @param $logLevel
	 * @param $webTrace
	 * @param $backTrace
	 * @return mixed
	 */
	public static function getLoggerInstance($logFile, $logLevel, $webTrace, $backTrace)
	{
		if ( !isset(self::$arrLoggers[$logFile]) ) {
			self::$arrLoggers[$logFile] = new Logger($logFile, $logLevel, $webTrace, $backTrace);
		}
		return self::$arrLoggers[$logFile];
	}

	/**
	 * 静态魔术方法获取log实例
	 *
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 * @throws \Exception
	 */
	public static function __callStatic($name, $arguments)
	{
		$logFile    = isset($arguments[0]) && is_string($arguments[0]) ? $arguments[0] : false;
		if ( empty($logFile) ) {
			throw new \Pol\Exception\LoggerFailed("Log {$logFile} file name is not allowed null");
		}
		$logLevel   = isset($arguments[1]) ? $arguments[1] : self::DEBUG;
		$webTrace   = isset($arguments[2]) ? $arguments[2] : false;
		$backTrace  = isset($arguments[3]) ? $arguments[3] : false;
		return self::getLoggerInstance($logFile, $logLevel, $webTrace, $backTrace);
	}

}