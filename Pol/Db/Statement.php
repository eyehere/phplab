<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | PDO组件的statament辅助类：    									      |
 * | 	开发者无需自己直接调用		                                          |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Db;

class Statement
{
	/**
	 * 默认的数据提取模式
	 * @var unknown
	 */
	protected static $_fetchMode = \PDO::FETCH_ASSOC;
	
	/**
	 * statement对象
	 * @var unknown
	 */
	protected $_pdoStatement = null;

	/**
	 * SQL
	 * @var unknown
	 */
	protected $_sql = null;

	/**
	 * 绑定参数
	 * @var array
	 */
	protected $_bindParam = array();

	/**
	 * 提取数据的方法
	 * @var array
	 */
	protected $_fetchMethod = array('fetch','fetchAll');

	/**
	 * 构造函数
	 */
	public function __construct()
	{

	}

	/**
	 * 接收statement对象和SQL 
	 * @param unknown $pdoStatement
	 * @param string $sql
	 */
	public function setStatement($pdoStatement, $sql = '')
	{
		$this->_pdoStatement = $pdoStatement;
		$this->_sql 		 = $sql;
	}

	/**
	 * 此函数不能用__call 因第二个参数为引用传值
	 *
	 * @param $column
	 * @param $param  绑定到SQL语句参数的PHP变量名
	 * @param int $type
	 * @return mixed
	 */
	public function bindColumn($column, &$param, $type = null)
	{
		$this->_bindParam[] = array(__FUNCTION__,$column,$param,$type);
		if( $type === null ) {
			return $this->_pdoStatement->bindColumn($column, $param);
		} else {
			return $this->_pdoStatement->bindColumn($column, $param, $type);
		}
	}

	/**
	 * 此函数不能用__call 因第二个参数为引用传值
	 *
	 * @param $column
	 * @param $variable     绑定到SQL语句参数的PHP变量名
	 * @param int $dataType
	 * @return mixed
	 */
	public function bindParam($column, &$variable, $dataType = null)
	{
		$this->_bindParam[] = array(__FUNCTION__,$column,$variable,$dataType);
		if( $dataType === null ) {
			return $this->_pdoStatement->_bindParam($column, $variable);
		} else {
			return $this->_pdoStatement->_bindParam($column, $variable, $dataType);
		}
	}

	/**
	 * statement对象执行SQL并且获取结果集
	 * @param unknown $method
	 * @param unknown $arguments
	 * @throws \Pol\Exception\PdoFailed
	 * @throws PDOException
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		if( !$this->_pdoStatement || !method_exists($this->_pdoStatement,$method) ) {
			$log = array('tip'=>'pdoStatement is null or method not exist','errorMethod'=>__METHOD__,'method'=>$method,'args'=>$arguments);
			\Pol\Log\PolLog::PdoLog()->error($log);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::DB_STATEMENT_UNKMOWN);
			throw new \Pol\Exception\PdoFailed('pdoStatement is null or method not exist,method:' . $method);
		}

		if( in_array($method,$this->_fetchMethod) ) {
			$this->_pdoStatement->setFetchMode( self::$_fetchMode );
		}

		try {
			$result = call_user_func_array(array($this->_pdoStatement, $method), $arguments);
		} catch ( \PDOException $e ) {
			$log = array();
			$log['tip'] = 'pdoStatement call exception';
			$log['method'] = $method;
			$log['sql'] = $this->_sql;
			$log['args'] = $arguments;
			$log['code'] = $e->getCode();
			$log['msg'] = $e->getMessage();
			\Pol\Log\PolLog::PdoLog()->error($log);
			\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::DB_EXEC_EXCEPTION);
			throw $e;
		}
		return $result;
	}
	
}