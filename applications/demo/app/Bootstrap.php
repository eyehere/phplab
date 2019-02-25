<?php

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
	
	public function _initDefault(\Yaf\Dispatcher $dispatcher)
	{
		$dispatcher->setDefaultModule('Index')
					->setDefaultController('Index')
					->setDefaultAction('index');
		\Yaf\Dispatcher::getInstance()->disableView();
	}
	
	public function _initPlugins(\Yaf\Dispatcher $dispatcher)
	{
	
	}
	
	public function __initConf(\Yaf\Dispatcher $dispatcher)
	{
		
	}
	
	public function _initDi(\Yaf\Dispatcher $dispatcher)
	{
		
	}
	
}