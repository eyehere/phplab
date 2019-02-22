<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 参数校验类：                                                           |
 * |    校验集合的数量和重复					                              |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Validate;

class DataList 
{

	/**
	 * 分隔符
	 * @var string
	 */
	protected static $_delimiter = '';

	/**
	 * 中间处理过程数组
	 * @var array
	 */
	protected static $_tempData = array();

	/**
	 * 元素类型
	 * @var array
	 */
	private static $_valueTypes = array(
			'int' 	 => true, 
			'float'  => true, 
			'enum' 	 => true, 
			'string' => true
	);

	/**
	 *
	 * @param mixed $data  待检查的数据
	 * @param unknown_type $type
	 * @param unknown_type $delimiter
	 * @param string $rule  rule规则。如"max,5;min,-3;"。
	 * @param enum $is_needed 空值限制选项，默认为1。
	 * @param enum $must_correct 验证限制选项，默认为1。
	 * @param mixed $default 默认值，默认为null。
	 * @return bool
	 * @throws \Pol\Exception\ValidateFailed
	 */
	public static function datalist($data, $type, $delimiter, $rules = '', $is_needed = 1, $must_correct = 1, $default = null) 
	{
		self::$_tempData = array();

		$type = strtolower($type);
		if ( !isset(self::$_valueTypes[$type]) ) {
			throw new \Pol\Exception\ValidateFailed('basic type should be only int,float,enum,string.');
		}

		$delimiter  = Validator::extractEscapedChars($delimiter);
		$rules      = Validator::extractEscapedChars($rules);
		$default    = $default === null ? null : Validator::extractEscapedChars($default);
		$isNeeded   = intval($is_needed);
		$mustCorrect = intval($must_correct);

		if ( !(is_string($delimiter) && $delimiter) ) {
			throw new \Pol\Exception\ValidateFailed('delimiter should be a valid string');
		}
		self::$_delimiter = $delimiter;
		self::$_tempData = explode($delimiter, $data);

		foreach ( self::$_tempData as $key => $value ) {
			Validator::$type($value, $rules, $isNeeded, $mustCorrect, $default);
		}

		return true;
	}

	/**
	 * 验证数据集元素容量是否小于等于某值
	 *
	 * @param mixed $data  待验证的数据集
	 * @param int $length  最大值阈值
	 * @return bool
	 * @throws \Pol\Exception\ValidateFailed
	 */
	public static function max($data, $length) 
	{
		if ( !is_array(self::$_tempData) ) {
			throw new \Pol\Exception\ValidateFailed('must use type rule to define the subtype and rules first of all');
		}
		return count(self::$_tempData) <= intval($length);
	}

	/**
	 * 验证数据集元素容量是否大于等于某值
	 *
	 * @param mixed $data  待验证的数据集
	 * @param int $length  最小值阈值
	 * @return bool
	 * @throws \Pol\Exception\ValidateFailed
	 */
	public static function min($data, $length) 
	{
		if( !is_array(self::$_tempData) ) {
			throw new \Pol\Exception\ValidateFailed('must use type rule to define the subtype and rules first of all');
		}
		return count(self::$_tempData) >= intval($length);
	}

	/**
	 * 验证数据集元素是否唯一
	 *
	 * @param mixed $data  待验证的数据集
	 * @return bool
	 * @throws \Pol\Exception\ValidateFailed
	 */
	public static function unique($data) 
	{
		if( !is_array(self::$_tempData) ) {
			throw new \Pol\Exception\ValidateFailed('must use type rule to define the subtype and rules first of all');
		}
		return count(array_unique(self::$_tempData)) === count(self::$_tempData);
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