<?php
/**
 * +----------------------------------------------------------------------+
 * | Pol (php optional library)                                           |
 * +----------------------------------------------------------------------+
 * | http调用类：	                                                          |
 * |    http client 调用				                                      |
 * +----------------------------------------------------------------------+
 * | Author: Weijun Lu  <yiming_6weijun@163.com>                          |
 * +----------------------------------------------------------------------+
 */

namespace Pol\Rpc\Http;

class Client
{
	//curl最大执行时间(1s)
	const CURL_EXEC_MAX_TIME = 1;
	
	/**
	 * curl句柄
	 * @var unknown
	 */
	protected $_curl = null;
	
	/**
	 * 设置请求失败进行重试的次数,默认不进行失败重试
	 * @var integer
	 */
	protected $_retryMax = 0;
	
	/**
	 * 连接超时时间(默认1500ms)
	 * @var integer
	 */
	protected $_connectTimeout = 1500;
	
	/**
	 * 设置curl执行时间(默认2000ms)
	 * @var integer
	 */
	protected $_exeTimeout = 2000;
	
	/**
	 * request url
	 * @var unknown
	 */
	protected $_reqUrl = null;
	
	/**
	 * request params
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * 可以自动附件上请求Id标识一次唯一请求
	 * @var string
	 */
	protected $_appendReqId = true;
	
	/**
	 * __construct
	 */
	public function __construct()
	{
		$this->_init();
	}
	
	/**
	 * 销毁curl
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * 初始化curl基础配置
	 */
	protected function _init()
	{
		$this->_curl = curl_init();
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);	//重定向
		curl_setopt($this->_curl, CURLOPT_MAXREDIRS, 3);			//这是重定向次数，防止死循环
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);	//设置是否直接输出结果
		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT_MS, $this->_connectTimeout);//设置连接超时时间
		curl_setopt($this->_curl, CURLOPT_TIMEOUT_MS, $this->_exeTimeout);	//设置curl执行时间
		curl_setopt($this->_curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);	//设置curl默认访问为IPv4
	}
	
	/**
	 * 添加个性化header
	 * @param array $header
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setHeader(array $header = array())
	{
		if ( $header && $this->_curl ) {
			curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $header);
		}
		return $this;
	}
	
	/**
	 * 添加
	 * @param unknown $proxy
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setProxy($proxy)
	{
		if ( $proxy ) {
			curl_setopt($this->_curl, CURLOPT_PROXY, $proxy);
		}
		return $this;
	}
	
	/**
	 * 添加cookie
	 * @param unknown $cookie
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setCookie($cookie)
	{
		curl_setopt($this->_curl, CURLOPT_COOKIE,$cookie);
		return $this;
	}
	
	/**
	 * 设置超时时间
	 * @param number $connectTimeout
	 * @param number $exeTimeout
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setTimeout($connectTimeout = 0, $exeTimeout = 0)
	{
		if ( $connectTimeout > 0 ) {
			curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT_MS, $connectTimeout);//设置连接超时时间
		}
		if ( $connectTimeout <= 1000 ) {
			curl_setopt($this->_curl, CURLOPT_NOSIGNAL, 1);//禁用信号，直接跳过DNS解析超时校验
		}
		if ( $exeTimeout > 0 ) {
			curl_setopt($this->_curl, CURLOPT_TIMEOUT_MS, $exeTimeout);//设置curl执行时间
		}
		return $this;
	}
	
	/**
	 * 设置失败的重试次数
	 * @param number $retryNum
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setRetry($retryNum = 0)
	{
		$this->_retryMax = intval($retryNum);
		return $this;
	}
	
	/**
	 * https在没有证书的时候 关闭验证证书来源和SSL加密算法校验
	 * @param string $bool
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setHttps($bool = false)
	{
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, $bool); // 对认证证书来源的检查
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, $bool); // 从证书中检查SSL加密算法是否存在
	
		return $this;
	}
	
	/**
	 * 添加请求ID 有参数对称加密或者类似微信第三方的请求不能添加
	 * @param string $bolAppend
	 * @return \Pol\Rpc\Http\Client
	 */
	public function setAppendReqId($bolAppend = true)
	{
		$this->_appendReqId = $boolAppend;
	
		return $this;
	}
	
	/**
	 * 获取client
	 * @return \Pol\Rpc\Http\unknown
	 */
	public function getClient()
	{
		return $this->_curl;
	}
	
	/**
	 * 获取请求概略信息
	 * @return string[]
	 */
	public function getReqIntro()
	{
		return array('url'=>$this->reqUrl,'params'=>$this->_params);
	}
	
	/**
	 * 获取请求URl
	 * @param unknown $reqUrl
	 * @return string
	 */
	protected function _getReqUrl($reqUrl)
	{
		//添加请求唯一id标示
		if ( $this->_appendReqId && strpos($reqUrl, 'req_id=') === false ) {
			$reqId = $this->_genReqId().'_'.microtime(true);
			if ( strpos($reqUrl, '?') === false ) {
				$reqUrl .= '?req_id='.$reqId;
			} else {
				$reqUrl .= '&req_id='.$reqId;
			}
		}
		return $reqUrl;
	}
	
	/**
	 * 发post请求
	 * @param unknown $url
	 * @param array $data
	 * @param boolean $bolEncode
	 */
	public function post($url, $params = array(), $bolEncode = true)
	{
		$this->reqUrl = $this->_getReqUrl($url);
		$this->_params= $params;
		
		if ( is_array($params) && $bolEncode ) {
			$params = http_build_query($params);
		}
		
		curl_setopt($this->_curl, CURLOPT_POST, true);
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($this->_curl, CURLOPT_URL, $this->_reqUrl);
		
		return $this;
	}
	
	/**
	 * get常用请求
	 * @param unknown $url
	 * @param array $params
	 */
	public function get($url, array $params = array())
	{
		if ( strpos($this->reqUrl, '?') === false ) {
			$url .= '?'.http_build_query($params);
		} else {
			$url .= '&'.http_build_query($params);
		}
		
		$this->reqUrl = $this->_getReqUrl($url);
		$this->_params= $params;
		
		curl_setopt($this->_curl, CURLOPT_HTTPGET, true);
		curl_setopt($this->_curl, CURLOPT_URL, $this->_reqUrl);
		
		return $this;
	}
	
	/**
	 * 生成请求ID
	 * @return string
	 */
	protected function _genReqId() 
	{
		$str = ((mt_rand() << 1) | (mt_rand() & 1) ^ microtime(true));
		return strtolower(base_convert($str, 10, 36));
	}
	
	/**
	 * 执行请求得到返回结果 
	 * @return number[]|string[]|mixed[]|NULL[]|unknown[]
	 */
	public function execute()
	{
		$retry 		= 0;
		$startTime 	= microtime(true);
		
		do{
			$res = array();
			$res['errno'] 	= (int)curl_errno($this->_curl);
			$res['error'] 	= curl_error($this->_curl);
			$res['response'] = curl_exec($this->_curl);
			if ( $res['errno'] && empty($res['error']) ) {
				$res['error'] = 'strError:'.curl_strerror($res['errno']);
			}
			$httpInfo = curl_getinfo($this->_curl);
			$res['httpInfo'] = $httpInfo;
			if ( $res['errno'] === 0 && isset($httpInfo['http_code']) && intval($httpInfo['http_code']) !== 200 ) {
				$res['errno']   = (int)$httpInfo['http_code'];
				$res['error']	= 'http response code:' . $httpInfo['http_code'];
			}
		//当code为28超时进行重试
		} while ( !empty($res['errno']) && ($res['errno'] == 28) && ++$retry <= $this->_retryMax );
		
		$endTime = microtime(true);
		$runTime = sprintf('%.3f',$endTime - $startTime);
	
		$log = array('url'=>$this->reqUrl,'params'=>$this->_params,'res'=>$res,'runTime'=>$runTime);
		
		if ( bccomp($runTime, self::CURL_EXEC_MAX_TIME, 2) > 1 ) {
			$log['tip'] = 'httpCurl spend time id too long';
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
		return $res;
	}
	
	/**
	 * 释放资源
	 */
	public function close()
	{
		if ( !empty($this->_curl) && is_resource($this->_curl) ) {
			curl_close($this->_curl);
		}
		$this->_curl = null;
	}
	
}