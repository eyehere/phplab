<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 事件管理实现类(面向切面)：                                                |
 * |    在某个阶段定义好事件，到合适的阶段进行触发单个或者多个事件                    |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Manager;

class Events
{
	/**
	 * 所有定义的事件
	 * @var array
	 */
	protected $_events = array();

	/**
	 * 附加事件
	 *
	 * @param $name
	 * @param $handler
	 * @param array $data
	 * @return $this
	 * @throws \Exception
	 */
	public function attach($name, $handler, $data = array())
	{
		if( !($handler instanceof \Closure) ) {
			throw new \Pol\Exception\EventFailed('Event handler must be an Closure Object');
		}

		if ( !isset($this->_events[$name]) ) {
			$this->_events[$name][] = array($handler, $data);
		} else {
			array_unshift($this->_events[$name], array($handler, $data));
		}
		return $this;
	}

	/**
	 * 解除绑定
	 *
	 * @param $name
	 * @param null $handler
	 * @return bool
	 */
	public function detach($name, $handler = null)
	{
		if ( empty($this->_events[$name]) ) {
			return false;
		}

		if ( $handler === null ) {
			unset($this->_events[$name]);
			return true;
		} else {
			$removed = false;

			// 遍历所有的 $handler
			foreach ( $this->_events[$name] as $i=>$event ) {
				if ( $event[0] === $handler ) {
					unset($this->_events[$name][$i]);
					$removed = true;
				}
			}
			if ($removed) {
				$this->_events[$name] = array_values($this->_events[$name]);
			}
			return $removed;
		}
	}

	/**
	 * 全部解除
	 *
	 * @param null $name
	 * @return bool
	 */
	public function detachAll($name = null)
	{
		if (is_null($name)) {
			$this->_events = null;
		} else {
			if(isset($this->_events[$name])){
				unset($this->_events[$name]);
			}
		}
		return true;
	}

	/**
	 * 触发事件
	 *
	 * @param $name
	 * @param $callback 匿名函数
	 */
	public function fire($name, $callback = null)
	{
		if( isset($this->_events[$name]) ) {
			foreach( $this->_events[$name] as $event ) {
				$class  = $event[0];
				$params = $event[1];
				$res 	= call_user_func_array($class,$params);
				if ( $callback && ($callback instanceof \Closure) ) {
					call_user_func($callback,$res);
				}
			}
		}
	}

	/**
	 * 获取监听事件
	 *
	 * @param $name
	 * @return array
	 */
	public function getListeners($name)
	{
		return isset($this->_events[$name]) ? $this->_events[$name] : array();
	}

}