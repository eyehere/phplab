<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 参数校验类：                                                           |
 * |    是否在枚举列表的校验					                              |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Validate;

class Enumer 
{
	/**
	 * 验证数据值是否在枚举列表中
	 *
	 * @param mixed $data  待验证的数据
	 * @param mixed $enumerates 枚举列表，多参数
	 * @return bool
	 */
	public static function enum($data, $enumerates) 
	{
		$args = func_get_args();
		array_shift($args);

		return in_array(strval($data), $args, true);
	}

	/**
	 * 强类型转换
	 *
	 * @param $data
	 * @return mixed
	 */
	public static function value($data) 
	{
		return $data;
	}
}