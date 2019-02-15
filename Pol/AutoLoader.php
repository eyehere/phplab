<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 自动加载类：无框架、命令行模式、与已有的自动加载规则不通的情况下引入此文件          |
 * | (使用的命名空间风格与yaf保持一致)                                          |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol;

class AutoLoader
{
	/**
	 * 目录分隔符
	 * @var string
	 */
	protected static $_ds = '';
	
	/**
	 * Pol类库的目录
	 * @var string
	 */
	protected static $_polDir = '';

	/**
	 * 文件名后缀
	 * @var string
	 */
	protected static $_suffix = '.php';
	
	/**
	 * 初始化自动加载类
	 */
	protected static function _init() 
	{	
		if ( empty(self::$_ds) ) {
			self::$_ds = DIRECTORY_SEPARATOR;
		}
		
		if ( empty(self::$_polDir) ) {
			self::$_polDir = dirname(__DIR__) . self::$_ds;
		}
	}

	/**
	 * 自动加载类
	 * @param string $className
	 * @return boolean
	 */
	public static function autoload($className) 
	{
		if ( class_exists($className, false) ) {
			return true;
		}

		if( strpos($className,'\\') !== false ) {
			$className = str_replace('\\',DS,$className);
			$className = ltrim($className,DS);
		}
		
		$file = self::$_polDir . $className . self::$suffix;

		if ( file_exists($file) ) {
			require_once $file;
			return true;
		}
		
		return false;	
	}

	/**
	 * 注册调用函数
	 */
	public static function register() 
	{	
		self::_init();
		spl_autoload_register(array('Pol\AutoLoader','autoload'),true,true);
	}
	
}

AutoLoader::register();