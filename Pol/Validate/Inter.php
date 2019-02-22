<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 参数校验类：                                                           |
 * |    Int类型的校验:类型、上下限、区间、长度		                              |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Validate;

class Inter {
	/**
	 * 默认规则（是否仅包含数字）
	 *
	 * @param string $data  待验证的数据
	 * @return bool
	 */
	public static function basic($data) 
	{
		return (bool) preg_match('/^-?[\d]+$/iD', $data, $match);
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
		if ( $leftValue >= $rightValue ) {
			$min = $rightValue;
			$max = $leftValue;
		} else {
			$min = $leftValue;
			$max = $rightValue;
		}

		return ($data <= $max && $data >= $min);
	}

	/**
	 * 验证数据长度（字符数）是否在某两值之间
	 *
	 * @param string $data  待验证的数据
	 * @param int $minLen  区间最小值，可选，默认为1。
	 * @param int $maxLen  区间最大值，可选，默认为null，不设区间最大值。
	 * @return bool
	 * @throws \Pol\Exception\ValidateFailed
	 */
	public static function len($data, $minLen = 1, $maxLen = null) 
	{
		if ( !$minLen ) {
			throw new \Pol\Exception\ValidateFailed('param_is_uncorrect');
		}

		if ($minLen && $maxLen) {
			if ($minLen > $maxLen) {
				$minLen = $minLen + $maxLen;
				$maxLen = $minLen - $maxLen;
				$minLen = $minLen - $maxLen;
			}

			if ($minLen == $maxLen) {
				$match_string = '/\d{' . $minLen . '}/';
			} else {
				$match_string = '/\d{' . $minLen . ',' . $maxLen . '}/';
			}
		} else {
			$match_string = '/\d{' . $minLen . ',}/';
		}

		return preg_match($match_string, $data, $match);
	}

	/**
	 * 类型明确的进行强类型的转换
	 *
	 * @param $data
	 * @return int
	 */
	public static function value($data) 
	{
		return intval($data);
	}

}