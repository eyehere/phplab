<?php

class ErrorController extends \Yaf\Controller_Abstract
{
	
	public function errorAction() 
	{
		$e = $this->getRequest()->getException();
		
		$error = array();
		$error['errno']	= $e->getCode();
		$error['errmsg']= $e->getMessage();
		$error['msg'] 	= '非法请求';
		$error['data']['meta'] = new \stdClass();
		echo json_encode($error);
	}
	
}