<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | http并发调用类：                                                       |
 * |    http client multi并发调用		                                      |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Rpc\Http;

class Multi
{
	//curl最大执行时间(1s)
	const CURL_EXEC_MAX_TIME = 1;

	/**
	 * 并发请求的curl资源队列
	 * @var array
	 */
	protected $_arrQueue = array();
	
	/**
	 * multi 构造函数
	 */
	public function __construct() {
		$this->_arrQueue = array();
	}
	
	/**
	 * @brief 注册请求实例
	 * @param string $identity
	 * @param curl $objClient
	 */
	public function register($identity, $objClient) {
		$this->_arrQueue[$identity] = $objClient;
		return $this;
	}
	
	/**
	 * @brief 并发执行多个http服务
	 * @return array
	 */
	public function execute() {
		if ( count($this->_arrQueue) === 1 ) {
			list($identity, $objClient) = each($this->_arrQueue);
			return array($identity => $objClient->execute());
		}
	
		return $this->_execute();
	}
	
	/**
	 * @brief 队列大于1时 并行执行多个请求
	 * @return array
	 */
	protected function _execute() {
		$arrRet = array();
		$startTime = microtime(true);
	
		$mh = curl_multi_init();
		foreach ( $this->_arrQueue as $identity=>$objClient ) {
			curl_multi_add_handle($mh, $objClient->getClient() );
		}
	
		$active = null;
		do
		{
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	
		while ($active && $mrc == CURLM_OK)
		{
			while (curl_multi_exec($mh, $active) === CURLM_CALL_MULTI_PERFORM);
	
			if (curl_multi_select($mh) != -1)
			{
				do {
					$mrc = curl_multi_exec($mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		
		$endTime = microtime(true);
		$runTime = sprintf('%.3f', $endTime-$startTime);
	
		foreach ($this->_arrQueue as $identity=>$objClient) {
			$ch = $objClient->getClient();
				
			$res = array();
			$res['errno']	= (int)curl_errno($ch);
			$res['error']	= curl_error($ch);
			$res['response']= curl_multi_getcontent($ch);
				
			$httpInfo = curl_getinfo($ch);
			
			curl_multi_remove_handle($mh,$ch);
			curl_close($ch);
				
			if ( $res['errno'] === 0 && isset($httpInfo['http_code']) && intval($httpInfo['http_code']) !== 200 ) {
				$res['errno']   = (int)$httpInfo['http_code'];
				$res['error']	= 'http response code:' . $httpInfo['http_code'];
			}
			$arrRet[$identity] = $res;
			
			$log = $objClient->getReqIntro();
			$log['res'] 	= $res;
			$log['runTime']	= $runTime;
			
			if ( bccomp($runTime, self::CURL_EXEC_MAX_TIME, 2) > 1 ) {
				$log['tip'] = 'http call spend time is too long';
				\Pol\Log\PolLog::curlLog()->warning($log);
			}
				
			if ( $res['errno'] !== 0 ) {
				$log['tip'] = 'httpCallFail';
				\Pol\Log\PolMonitor::reportCount(\Pol\Log\PolMonitor::HTTP_CALL_EXCEPTION);
				\Pol\Log\PolLog::curlLog()->error($log);
			} else {
				$log['tip'] = 'httpSuccess';
				\Pol\Log\PolLog::curlLog()->debug($log);
			}
			
		}
		curl_multi_close($mh);
		
		return $arrRet;
	}
	
}