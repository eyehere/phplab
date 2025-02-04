<?php
final class Yaf_Application {
	/* properties */
	protected $config = NULL;
	protected $dispatcher = NULL;
	static protected $_app = NULL;
	protected $_modules = NULL;
	protected $_running = "";
	protected $_environ = "product";
	protected $_err_no = "0";
	protected $_err_msg = "";

	/* methods */
	public function __construct($config, $environ = NULL) {
	}
	public function run() {
	}
	public function execute($entry, $_ = "...") {
	}
	public static function app() {
	}
	public function environ() {
	}
	public function bootstrap($bootstrap = NULL) {
	}
	public function getConfig() {
	}
	public function getModules() {
	}
	public function getDispatcher() {
	}
	public function setAppDirectory($directory) {
	}
	public function getAppDirectory() {
	}
	public function getLastErrorNo() {
	}
	public function getLastErrorMsg() {
	}
	public function clearLastError() {
	}
	public function __destruct() {
	}
	private function __clone() {
	}
	private function __sleep() {
	}
	private function __wakeup() {
	}
}
abstract class Yaf_Bootstrap_Abstract {
}
final class Yaf_Dispatcher {
	/* properties */
	protected $_router = NULL;
	protected $_view = NULL;
	protected $_request = NULL;
	protected $_plugins = NULL;
	static protected $_instance = NULL;
	protected $_auto_render = "1";
	protected $_return_response = "";
	protected $_instantly_flush = "";
	protected $_default_module = NULL;
	protected $_default_controller = NULL;
	protected $_default_action = NULL;

	/* methods */
	private function __construct() {
	}
	private function __clone() {
	}
	private function __sleep() {
	}
	private function __wakeup() {
	}
	public function enableView() {
	}
	public function disableView() {
	}
	public function initView($templates_dir, array $options = NULL) {
	}
	public function setView($view) {
	}
	public function setRequest($request) {
	}
	public function getApplication() {
	}
	public function getRouter() {
	}
	public function getRequest() {
	}
	public function setErrorHandler($callback, $error_types) {
	}
	public function setDefaultModule($module) {
	}
	public function setDefaultController($controller) {
	}
	public function setDefaultAction($action) {
	}
	public function returnResponse($flag) {
	}
	public function autoRender($flag) {
	}
	public function flushInstantly($flag) {
	}
	public static function getInstance() {
	}
	public function dispatch($request) {
	}
	public function throwException($flag = NULL) {
	}
	public function catchException($flag = NULL) {
	}
	public function registerPlugin($plugin) {
	}
}
final class Yaf_Loader {
	/* properties */
	protected $_library = NULL;
	protected $_global_library = NULL;
	static protected $_instance = NULL;

	/* methods */
	private function __construct() {
	}
	private function __clone() {
	}
	private function __sleep() {
	}
	private function __wakeup() {
	}
	public function autoload($class_name) {
	}
	public static function getInstance($local_library_path = NULL, $global_library_path = NULL) {
	}
	public function registerLocalNamespace($name_prefix) {
	}
	public function getLocalNamespace() {
	}
	public function clearLocalNamespace() {
	}
	public function isLocalName($class_name) {
	}
	public static function import($file) {
	}
	public function setLibraryPath($library_path, $is_global = NULL) {
	}
	public function getLibraryPath($is_global = NULL) {
	}
}
abstract class Yaf_Request_Abstract {
	/* constants */
	const SCHEME_HTTP = "http";
	const SCHEME_HTTPS = "https";

	/* properties */
	public $module = NULL;
	public $controller = NULL;
	public $action = NULL;
	public $method = NULL;
	protected $params = NULL;
	protected $language = NULL;
	protected $_exception = NULL;
	protected $_base_uri = "";
	protected $uri = "";
	protected $dispatched = "";
	protected $routed = "";

	/* methods */
	public function isGet() {
	}
	public function isPost() {
	}
	public function isDelete() {
	}
	public function isPatch() {
	}
	public function isPut() {
	}
	public function isHead() {
	}
	public function isOptions() {
	}
	public function isCli() {
	}
	public function isXmlHttpRequest() {
	}
	public function getServer($name, $default = NULL) {
	}
	public function getEnv($name, $default = NULL) {
	}
	public function setParam($name, $value = NULL) {
	}
	public function getParam($name, $default = NULL) {
	}
	public function getParams() {
	}
	public function getException() {
	}
	public function getModuleName() {
	}
	public function getControllerName() {
	}
	public function getActionName() {
	}
	public function setModuleName($module) {
	}
	public function setControllerName($controller) {
	}
	public function setActionName($action) {
	}
	public function getMethod() {
	}
	public function getLanguage() {
	}
	public function setBaseUri($uri) {
	}
	public function getBaseUri() {
	}
	public function getRequestUri() {
	}
	public function setRequestUri($uri) {
	}
	public function isDispatched() {
	}
	public function setDispatched() {
	}
	public function isRouted() {
	}
	public function setRouted($flag = NULL) {
	}
}
class Yaf_Request_Http extends Yaf_Request_Abstract {
	/* properties */
	public $module = NULL;
	public $controller = NULL;
	public $action = NULL;
	public $method = NULL;
	protected $params = NULL;
	protected $language = NULL;
	protected $_exception = NULL;
	protected $_base_uri = "";
	protected $uri = "";
	protected $dispatched = "";
	protected $routed = "";

	/* methods */
	public function getQuery() {
	}
	public function getRequest() {
	}
	public function getPost() {
	}
	public function getCookie() {
	}
	public function getRaw() {
	}
	public function getFiles() {
	}
	public function get() {
	}
	public function isXmlHttpRequest() {
	}
	public function __construct() {
	}
	private function __clone() {
	}
	public function isGet() {
	}
	public function isPost() {
	}
	public function isDelete() {
	}
	public function isPatch() {
	}
	public function isPut() {
	}
	public function isHead() {
	}
	public function isOptions() {
	}
	public function isCli() {
	}
	public function getServer($name, $default = NULL) {
	}
	public function getEnv($name, $default = NULL) {
	}
	public function setParam($name, $value = NULL) {
	}
	public function getParam($name, $default = NULL) {
	}
	public function getParams() {
	}
	public function getException() {
	}
	public function getModuleName() {
	}
	public function getControllerName() {
	}
	public function getActionName() {
	}
	public function setModuleName($module) {
	}
	public function setControllerName($controller) {
	}
	public function setActionName($action) {
	}
	public function getMethod() {
	}
	public function getLanguage() {
	}
	public function setBaseUri($uri) {
	}
	public function getBaseUri() {
	}
	public function getRequestUri() {
	}
	public function setRequestUri($uri) {
	}
	public function isDispatched() {
	}
	public function setDispatched() {
	}
	public function isRouted() {
	}
	public function setRouted($flag = NULL) {
	}
}
final class Yaf_Request_Simple extends Yaf_Request_Abstract {
	/* constants */
	const SCHEME_HTTP = "http";
	const SCHEME_HTTPS = "https";

	/* properties */
	public $module = NULL;
	public $controller = NULL;
	public $action = NULL;
	public $method = NULL;
	protected $params = NULL;
	protected $language = NULL;
	protected $_exception = NULL;
	protected $_base_uri = "";
	protected $uri = "";
	protected $dispatched = "";
	protected $routed = "";

	/* methods */
	public function __construct() {
	}
	private function __clone() {
	}
	public function getQuery() {
	}
	public function getRequest() {
	}
	public function getPost() {
	}
	public function getCookie() {
	}
	public function getFiles() {
	}
	public function get() {
	}
	public function isXmlHttpRequest() {
	}
	public function isGet() {
	}
	public function isPost() {
	}
	public function isDelete() {
	}
	public function isPatch() {
	}
	public function isPut() {
	}
	public function isHead() {
	}
	public function isOptions() {
	}
	public function isCli() {
	}
	public function getServer($name, $default = NULL) {
	}
	public function getEnv($name, $default = NULL) {
	}
	public function setParam($name, $value = NULL) {
	}
	public function getParam($name, $default = NULL) {
	}
	public function getParams() {
	}
	public function getException() {
	}
	public function getModuleName() {
	}
	public function getControllerName() {
	}
	public function getActionName() {
	}
	public function setModuleName($module) {
	}
	public function setControllerName($controller) {
	}
	public function setActionName($action) {
	}
	public function getMethod() {
	}
	public function getLanguage() {
	}
	public function setBaseUri($uri) {
	}
	public function getBaseUri() {
	}
	public function getRequestUri() {
	}
	public function setRequestUri($uri) {
	}
	public function isDispatched() {
	}
	public function setDispatched() {
	}
	public function isRouted() {
	}
	public function setRouted($flag = NULL) {
	}
}
abstract class Yaf_Response_Abstract {
	/* constants */
	const DEFAULT_BODY = "content";

	/* properties */
	protected $_header = NULL;
	protected $_body = NULL;
	protected $_sendheader = "";

	/* methods */
	public function __construct() {
	}
	public function __destruct() {
	}
	private function __clone() {
	}
	public function __toString() {
	}
	public function setBody($body, $name = NULL) {
	}
	public function appendBody($body, $name = NULL) {
	}
	public function prependBody($body, $name = NULL) {
	}
	public function clearBody($name = NULL) {
	}
	public function getBody($name = NULL) {
	}
	public function response() {
	}
}
class Yaf_Response_Http extends Yaf_Response_Abstract {
	/* constants */
	const DEFAULT_BODY = "content";

	/* properties */
	protected $_header = NULL;
	protected $_body = NULL;
	protected $_sendheader = "1";
	protected $_response_code = "0";

	/* methods */
	public function setHeader($name, $value, $rep = NULL, $response_code = NULL) {
	}
	public function setAllHeaders($headers) {
	}
	public function getHeader($name = NULL) {
	}
	public function clearHeaders() {
	}
	public function setRedirect($url) {
	}
	public function response() {
	}
	public function __construct() {
	}
	public function __destruct() {
	}
	private function __clone() {
	}
	public function __toString() {
	}
	public function setBody($body, $name = NULL) {
	}
	public function appendBody($body, $name = NULL) {
	}
	public function prependBody($body, $name = NULL) {
	}
	public function clearBody($name = NULL) {
	}
	public function getBody($name = NULL) {
	}
}
class Yaf_Response_Cli extends Yaf_Response_Abstract {
	/* constants */
	const DEFAULT_BODY = "content";

	/* properties */
	protected $_header = NULL;
	protected $_body = NULL;
	protected $_sendheader = "";

	/* methods */
	public function __construct() {
	}
	public function __destruct() {
	}
	private function __clone() {
	}
	public function __toString() {
	}
	public function setBody($body, $name = NULL) {
	}
	public function appendBody($body, $name = NULL) {
	}
	public function prependBody($body, $name = NULL) {
	}
	public function clearBody($name = NULL) {
	}
	public function getBody($name = NULL) {
	}
	public function response() {
	}
}
abstract class Yaf_Controller_Abstract {
	/* properties */
	public $actions = NULL;
	protected $_module = NULL;
	protected $_name = NULL;
	protected $_request = NULL;
	protected $_response = NULL;
	protected $_invoke_args = NULL;
	protected $_view = NULL;

	/* methods */
	protected function render($tpl, array $parameters = NULL) {
	}
	protected function display($tpl, array $parameters = NULL) {
	}
	public function getRequest() {
	}
	public function getResponse() {
	}
	public function getModuleName() {
	}
	public function getView() {
	}
	public function initView(array $options = NULL) {
	}
	public function setViewpath($view_directory) {
	}
	public function getViewpath() {
	}
	public function forward($module, $controller = NULL, $action = NULL, array $parameters = NULL) {
	}
	public function redirect($url) {
	}
	public function getInvokeArgs() {
	}
	public function getInvokeArg($name) {
	}
	final public function __construct() {
	}
	final private function __clone() {
	}
}
abstract class Yaf_Action_Abstract extends Yaf_Controller_Abstract {
	/* properties */
	public $actions = NULL;
	protected $_module = NULL;
	protected $_name = NULL;
	protected $_request = NULL;
	protected $_response = NULL;
	protected $_invoke_args = NULL;
	protected $_view = NULL;
	protected $_controller = NULL;

	/* methods */
	abstract public function execute();
	public function getController() {
	}
	protected function render($tpl, array $parameters = NULL) {
	}
	protected function display($tpl, array $parameters = NULL) {
	}
	public function getRequest() {
	}
	public function getResponse() {
	}
	public function getModuleName() {
	}
	public function getView() {
	}
	public function initView(array $options = NULL) {
	}
	public function setViewpath($view_directory) {
	}
	public function getViewpath() {
	}
	public function forward($module, $controller = NULL, $action = NULL, array $parameters = NULL) {
	}
	public function redirect($url) {
	}
	public function getInvokeArgs() {
	}
	public function getInvokeArg($name) {
	}
	final public function __construct() {
	}
	final private function __clone() {
	}
}
abstract class Yaf_Config_Abstract {
	/* properties */
	protected $_config = NULL;
	protected $_readonly = "1";

	/* methods */
	abstract public function get();
	abstract public function set();
	abstract public function readonly();
	abstract public function toArray();
}
final class Yaf_Config_Ini extends Yaf_Config_Abstract implements Iterator, Traversable, ArrayAccess, Countable {
	/* properties */
	protected $_config = NULL;
	protected $_readonly = "1";

	/* methods */
	public function __construct($config_file, $section = NULL) {
	}
	public function __isset($name) {
	}
	public function get($name = NULL) {
	}
	public function set($name, $value) {
	}
	public function count() {
	}
	public function rewind() {
	}
	public function current() {
	}
	public function next() {
	}
	public function valid() {
	}
	public function key() {
	}
	public function toArray() {
	}
	public function readonly() {
	}
	public function offsetUnset($name) {
	}
	public function offsetGet($name) {
	}
	public function offsetExists($name) {
	}
	public function offsetSet($name, $value) {
	}
	public function __get($name = NULL) {
	}
	public function __set($name, $value) {
	}
}
final class Yaf_Config_Simple extends Yaf_Config_Abstract implements Iterator, Traversable, ArrayAccess, Countable {
	/* properties */
	protected $_config = NULL;
	protected $_readonly = "";

	/* methods */
	public function __construct($config_file, $section = NULL) {
	}
	public function __isset($name) {
	}
	public function get($name = NULL) {
	}
	public function set($name, $value) {
	}
	public function count() {
	}
	public function offsetUnset($name) {
	}
	public function rewind() {
	}
	public function current() {
	}
	public function next() {
	}
	public function valid() {
	}
	public function key() {
	}
	public function readonly() {
	}
	public function toArray() {
	}
	public function __set($name, $value) {
	}
	public function __get($name = NULL) {
	}
	public function offsetGet($name) {
	}
	public function offsetExists($name) {
	}
	public function offsetSet($name, $value) {
	}
}
class Yaf_View_Simple implements Yaf_View_Interface {
	/* properties */
	protected $_tpl_vars = NULL;
	protected $_tpl_dir = NULL;
	protected $_options = NULL;

	/* methods */
	final public function __construct($template_dir, array $options = NULL) {
	}
	public function __isset($name) {
	}
	public function get($name = NULL) {
	}
	public function assign($name, $value = NULL) {
	}
	public function render($tpl, $tpl_vars = NULL) {
	}
	public function eval($tpl_str, $vars = NULL) {
	}
	public function display($tpl, $tpl_vars = NULL) {
	}
	public function assignRef($name, &$value) {
	}
	public function clear($name = NULL) {
	}
	public function setScriptPath($template_dir) {
	}
	public function getScriptPath() {
	}
	public function __get($name = NULL) {
	}
	public function __set($name, $value = NULL) {
	}
}
final class Yaf_Router {
	/* properties */
	protected $_routes = NULL;
	protected $_current = NULL;

	/* methods */
	public function __construct() {
	}
	public function addRoute() {
	}
	public function addConfig() {
	}
	public function route() {
	}
	public function getRoute() {
	}
	public function getRoutes() {
	}
	public function getCurrentRoute() {
	}
}
class Yaf_Route_Static implements Yaf_Route_Interface {
	/* methods */
	public function match($uri) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
final class Yaf_Route_Simple implements Yaf_Route_Interface {
	/* properties */
	protected $controller = NULL;
	protected $module = NULL;
	protected $action = NULL;

	/* methods */
	public function __construct($module_name, $controller_name, $action_name) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
final class Yaf_Route_Supervar implements Yaf_Route_Interface {
	/* properties */
	protected $_var_name = NULL;

	/* methods */
	public function __construct($supervar_name) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
final class Yaf_Route_Rewrite implements Yaf_Route_Interface {
	/* properties */
	protected $_route = NULL;
	protected $_default = NULL;
	protected $_verify = NULL;

	/* methods */
	public function __construct($match, array $route, array $verify = NULL) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
final class Yaf_Route_Regex implements Yaf_Route_Interface {
	/* properties */
	protected $_route = NULL;
	protected $_default = NULL;
	protected $_maps = NULL;
	protected $_verify = NULL;
	protected $_reverse = NULL;

	/* methods */
	public function __construct($match, array $route, array $map = NULL, array $verify = NULL, $reverse = NULL) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
final class Yaf_Route_Map implements Yaf_Route_Interface {
	/* properties */
	protected $_ctl_router = "";
	protected $_delimiter = NULL;

	/* methods */
	public function __construct($controller_prefer = NULL, $delimiter = NULL) {
	}
	public function route($request) {
	}
	public function assemble(array $info, array $query = NULL) {
	}
}
abstract class Yaf_Plugin_Abstract {
	/* methods */
	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
	public function preResponse(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
	}
}
final class Yaf_Registry {
	/* properties */
	static protected $_instance = NULL;
	protected $_entries = NULL;

	/* methods */
	private function __construct() {
	}
	private function __clone() {
	}
	public static function get($name) {
	}
	public static function has($name) {
	}
	public static function set($name, $value) {
	}
	public static function del($name) {
	}
}
final class Yaf_Session implements Iterator, Traversable, ArrayAccess, Countable {
	/* properties */
	static protected $_instance = NULL;
	protected $_session = NULL;
	protected $_started = "";

	/* methods */
	private function __construct() {
	}
	private function __clone() {
	}
	private function __sleep() {
	}
	private function __wakeup() {
	}
	public static function getInstance() {
	}
	public function start() {
	}
	public function get($name) {
	}
	public function has($name) {
	}
	public function set($name, $value) {
	}
	public function del($name) {
	}
	public function count() {
	}
	public function rewind() {
	}
	public function next() {
	}
	public function current() {
	}
	public function key() {
	}
	public function valid() {
	}
	public function clear() {
	}
	public function offsetGet($name) {
	}
	public function offsetSet($name, $value) {
	}
	public function offsetExists($name) {
	}
	public function offsetUnset($name) {
	}
	public function __get($name) {
	}
	public function __isset($name) {
	}
	public function __set($name, $value) {
	}
	public function __unset($name) {
	}
}
class Yaf_Exception extends Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_StartupError extends Yaf_Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_RouterFailed extends Yaf_Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_DispatchFailed extends Yaf_Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_LoadFailed extends Yaf_Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_LoadFailed_Module extends Yaf_Exception_LoadFailed implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_LoadFailed_Controller extends Yaf_Exception_LoadFailed implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_LoadFailed_Action extends Yaf_Exception_LoadFailed implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_LoadFailed_View extends Yaf_Exception_LoadFailed implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
class Yaf_Exception_TypeError extends Yaf_Exception implements Throwable {
	/* properties */
	protected $file = NULL;
	protected $line = NULL;
	protected $message = NULL;
	protected $code = "0";
	protected $previous = NULL;

	/* methods */
	final private function __clone() {
	}
	public function __construct($message = NULL, $code = NULL, $previous = NULL) {
	}
	public function __wakeup() {
	}
	final public function getMessage() {
	}
	final public function getCode() {
	}
	final public function getFile() {
	}
	final public function getLine() {
	}
	final public function getTrace() {
	}
	final public function getPrevious() {
	}
	final public function getTraceAsString() {
	}
	public function __toString() {
	}
}
interface Yaf_View_Interface {
	/* methods */
	abstract public function assign();
	abstract public function display();
	abstract public function render();
	abstract public function setScriptPath();
	abstract public function getScriptPath();
}
interface Yaf_Route_Interface {
	/* methods */
	abstract public function route();
	abstract public function assemble();
}
