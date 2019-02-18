<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | 数据库工具辅助类：快速生成Pdo增删查改所需的SQL								  |
 * | 		Assist  (select语句相对比较复杂，暂不提供生成工具)      	          |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Db;

class Assist
{
	/**
	 * 序列号
	 * @var integer
	 */
	protected static $_serial = 0;

	/**
	 * 数据表
	 * @var unknown
	 */
	protected $_table = null;
	
	/**
	 * SQL绑定的values
	 * @var array
	 */
	protected $_bindingVals = array();
	
	/**
	 * __construct
	 */
	public function __construct()
	{
		
	}

	/**
	 * 数据库表名字
	 * @param unknown $table
	 * @return \Pol\Db\Pdo
	 */
	public function setTable($table) 
	{
		$this->_table = '`' . $table . '`';
		return $this;
	}

	/**
	 * 生成绑定形式的半SQL
	 * 用于where条件的生成 或者 update set的半SQL生成
	 * @param array $data key=>value形式的键值对数组
	 * @return array('fields'=>array(),'values'=>array())
	 */
	public function genBindings(array $data){
		$bindings = array();
		foreach($data as $column => $value) {
			$bindColumn = isset($this->_bindingVals['values'][$column]) ? 
							$column.'_'.self::$_serial++ : $column;
			$bindings['fields'][] = '`' . $column . '`' . " = :". $bindColumn;
			$bindings['values'][':'.$bindColumn] = $value;
		}
		$this->_bindingVals = $bindings['values'];
		return $bindings;
	}
	
	/**
	 * insert 插入操作预绑定SQL及绑定生成工具
	 * @param array $data
	 * @param string $method
	 * @return array(sql=>'', 'bindings'=>array())
	 */
	public function genInsert(array $data,$method='INSERT')
	{
		$result = array();
		if( !empty($data) ) {
			$this->_bindingVals = array();
			$setData = $this->genBindings($data);
			$fieldsvals = implode(",", array_keys($setData['values']));
			$keys     =  array_keys($data);
			$fields = '`' . implode('`,`', $keys) . '`';
			if ( strtoupper($method) == 'REPLACE' ) {
				$result['sql'] = "REPLACE INTO ". $this->_table ." (". $fields .") VALUES (".$fieldsvals.")";
			} else {
				$result['sql'] = "INSERT INTO ". $this->_table ." (". $fields .") VALUES (".$fieldsvals.")";
			}
			$result['bindings'] = $this->_bindingVals;
		}
		return $result;
	}

	/**
	 * update 更新操作预绑定SQL及绑定生成工具
	 * @param $data array
	 * @param $where array
	 * @return @return array(sql=>'', 'bindings'=>array())
	 */
	public function genUpdate(array $data, $where=array()) 
	{
		if( empty($where) ) {
			return array();
		}

		$result = array();
		$this->_bindingVals = array();

		$where = $this->genBindings($where);
		$wfield = implode(" AND ", $where['fields']);

		$setData = $this->genBindings($data);
		$fieldsvals = implode(" , ", $setData['fields']);

		if( !empty($fieldsvals) ) {
			$result['sql'] = "UPDATE " . $this->_table .  " SET " . $fieldsvals . " WHERE " . $wfield;
		}
		$result['bindings'] = $this->_bindingVals;
		return $result;
	}
	
	/**
	 * 删除操作预绑定SQL及绑定生成工具
	 * @param array $where
	 * @return string[]
	 * @return @return array(sql=>'', 'bindings'=>array())
	 */
	public function genDelete(array $where)
	{
		if( empty($where) ) {
			return array();
		}
	
		$result = array();
		$this->_bindingVals = array();
		
		$where = $this->genBindings($where);
		$wfield = implode(" AND ", $where['fields']);
	
		if( !empty($wfield) ) {
			$result['sql'] = "DELETE FROM " . $this->_table . " WHERE " . $wfield;
		}
		$result['bindings'] = $this->_bindingVals;
		return $result;
	}

}