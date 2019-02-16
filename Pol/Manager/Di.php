<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 依赖注入实现类：                                                        |
 * |    主要用来做上下文配置解耦、单例实现、延迟调用、延迟实例化                     |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Manager;

class Di
{
	/**
	 * 依赖注入列表
	 * @var array
	 */
	protected static $_bindings = array();

	/**
	 * 已经实例化的列表
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * 获取服务
	 *
	 * @param $name
	 * @return mixed|null|object
	 */
	public static function get($name)
	{
		//先从已经实例化的列表中查找
		if( isset(self::$_instances[$name]) ) {
			return self::$_instances[$name];
		}

		//检测有没有注册
		if( !isset(self::$_bindings[$name]) ) {
			return null;
		}
		
		$concrete 	= self::$_bindings[$name]['class'];  //对象具体注册内容
		$params 	= self::$_bindings[$name]['params']; //配置
		$obj 		= null;
		//匿名函数方式
		if( $concrete instanceof \Closure ) {
			$obj = call_user_func_array($concrete, $params);
		} elseif ( is_string($concrete) ) { //字符串方式
			if( empty($params) ) {
				$obj = new $concrete;
			} else {
				//带参数的类实例化，使用反射
				$class 	= new \ReflectionClass($concrete);
				$obj 	= $class->newInstanceArgs($params);
			}
		}
		//如果是共享服务，则写入_instances列表，下次直接取回
		if( self::$_bindings[$name]['shared'] == true && $obj ) {
			self::$_instances[$name] = $obj;
		}

		return $obj;
	}

	/**
	 * 检测是否已经存在
	 *
	 * @param $name
	 * @return bool
	 */
	public static function has($name)
	{
		return isset(self::$_bindings[$name]) || isset(self::$_instances[$name]);
	}

	/**
	 * 卸载
	 *
	 * @param $name
	 * @return bool
	 */
	public static function remove($name)
	{
		unset(self::$_bindings[$name],self::$_instances[$name]);
	}

	/**
	 * 设置服务
	 * 已经存在的不允许被再次设置，通过has检测是否存在,可以用remove删除
	 * (类库层面不能帮用户判断是否能删除)
	 * 
	 * @param $name
	 * @param $class
	 * @param $params
	 * @return bool
	 */
	public static function set($name, $class, $params = array())
	{
		self::_registerService($name, $class, $params);
	}

	/**
	 * 设置共享(单例)
	 *
	 * @param $name
	 * @param $class
	 * @param $params
	 */
	public static function setShared($name, $class, $params = array())
	{
		self::_registerService($name, $class, $params, true);
	}

	/**
	 * 注册服务
	 *
	 * @param $name
	 * @param $class
	 * @param $params
	 * @param bool|false $shared
	 */
	private static function _registerService($name, $class, $params = array(), $shared = false)
	{
		if ( self::has($name) ) {
			throw new \Pol\Exception\DiFailed('(' . $name . ') is already registered');
		}
		if( !($class instanceof \Closure) && is_object($class) ) {
			self::$_instances[$name] = $class;
		} else {
			self::$_bindings[$name]  = array('class'=>$class,'shared'=>$shared,'params'=>$params);
		}
	}
	
}