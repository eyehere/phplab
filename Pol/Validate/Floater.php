<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 参数校验类：                                                           |
 * |    Float类型的校验:类型、上下限、区间		                              |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Validate;

class Floater {

	/**
	 * 基础校验是否是数值类型
	 * @param unknown $data
	 * @return boolean
	 */
	public static function basic($data) 
	{
		return is_numeric($data);
	}

	/**
	 * 验证是否大于等于某值
	 *
	 * @param string $data  待验证的数据
	 * @param int $min  最小值阈值
	 * @return bool
	 */
	public static function min($data, $min) 
	{
		return $data >= $min;
	}

	/**
	 * 验证是否小于等于某值
	 *
	 * @param string $data  待验证的数据
	 * @param int $max  最大值阈值
	 * @return bool
	 */
	public static function max($data, $max) 
	{
		return $data <= $max;
	}

	/**
	 * 验证数据值是否在某两值之间（含）
	 *
	 * @param string $data  待验证的数据
	 * @param int $leftValue  区间最小值
	 * @param int $rightValue  区间最大值
	 * @return bool
	 */
	public static function range($data, $leftValue, $rightValue) 
	{
		if ($leftValue >= $rightValue) {
			$min = $rightValue;
			$max = $leftValue;
		} else {
			$min = $leftValue;
			$max = $rightValue;
		}

		return ($data <= $max && $data >= $min);
	}

	/**
	 * 强类型转换
	 *
	 * @param $data
	 * @return float
	 */
	public static function value($data) 
	{
		return floatval($data);
	}
}