<?php

class ModuleController extends \Yaf\Controller_Abstract
{
	const MODULE = 'modules';

	const ACTIONS = 'actions';

	const SUFFIX = '.php';
	
	public $actions = array();

	public function init()
	{
		$request	= $this->getRequest();
		$module		= $request->module;
		$controller = $request->controller;
		$action 	= $request->action;

		$list 	= array(self::MODULE, $module, self::ACTIONS, $controller, $action);
		$class 	= implode(DS, $list);
		$this->actions[$request->action] = $class.self::SUFFIX;
	}
}