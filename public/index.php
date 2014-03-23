<?php

use \Phalcon\Mvc\Dispatcher as PhDispatcher;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

Phalcon\Mvc\Model::setup(['exceptionOnFailedSave' => true]);
ini_set('phalcon.orm.virtual_foreign_keys', false);

class MyRouter extends \Phalcon\Mvc\Router
{
	public function setModule($module)
	{
		$this->_module = $module;
	}
}

/*
$f = new ReflectionMethod('\Phalcon\Mvc\View\Engine\Volt\Compiler::_compileSource');
var_dump($f->getParameters());
var_dump($f->getNumberOfParameters());
*/

/**
 * LiveCart front controller
 *
 * @author Integry Systems
 * @package application
 */
try
{
	define('__ROOT__', dirname(__DIR__) . '/');

	//Register an autoloader
	$loader = new \Phalcon\Loader();
	$loader->registerDirs(array(
		//__ROOT__ . 'application/controller/',
		//__ROOT__ . 'application/controller/backend',
		//__ROOT__ . 'module/mrfeedback/application/controller/',
		__ROOT__ . 'application/model/',
		__ROOT__ . 'application/',
		__ROOT__ . 'module/',
		__ROOT__ . 'library/',
		__ROOT__ . 'application/helper/',
	))->register();

	//Create a DI
	$di = new Phalcon\DI\FactoryDefault();

	$di->set('loader', $loader);

	$view = new \Phalcon\Mvc\View();
	$view->setViewsDir(__ROOT__ . 'application/view/');

	$view->registerEngines(array(
		".tpl" => function($view, $di)
		{
			$dir = __ROOT__ . 'cache/templates/';
			if (!file_exists($dir))
			{
				mkdir($dir);
				chmod($dir, 0777);
			}

			$volt = new LiveVolt($view, $di);
			$volt->setOptions(array('compiledPath' => __ROOT__ . 'cache/templates/', 'compileAlways' => true));
			return $volt;
		}
	));

	//Setting up the view component
	$di->set('view', $view);

 	// Specify routes for modules
	$di->set('router', function () {

		$handle = '[^\.\047]{0,}';

		$router = new MyRouter();
		$router->setDefaultModule("frontend");

		include __ROOT__ . 'application/configuration/route/core.php';

		return $router;
	});

	$di->set('request', function() use ($di)
	{
		$request = new \LiveCartRequest();
		$request->setDI($di);
		return $request;
	});
	
	// configuration handler
	$di->set('config', function() use ($di)
	{
		return new \system\Config($di);
	});

	// Caching
	$di->set('cache', function()
	{
		$frontCache = new Phalcon\Cache\Frontend\Output(array(
			"lifetime" => 0
		));

		return new Phalcon\Cache\Backend\File($frontCache, array(
    		"cacheDir" => __ROOT__ . "/cache/"
			));
	});

	$di->set('modelCache', function() use ($di)
	{
		return $di->get('cache');
	});

	$di->set('modelsMetadata', function() 
	{
		// Create a meta-data manager with APC
		$metaData = new \Phalcon\Mvc\Model\MetaData\Apc(array(
			"lifetime" => 86400,
			"prefix"   => "my-prefix"
		));

		return $metaData;
	});

	$di->set('url', function(){
		$url = new Phalcon\Mvc\Url();

		$URI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$path = parse_url($URI,  PHP_URL_PATH);

		//var_dump($_REQUEST['_url']);
		//die($base);

		$base = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, -1 * strlen('/public/index.php')) . '/';
		$url->setBaseUri($base);

		return $url;
	});

	$di->set('sessionUser', function() use ($di) {
		return new \user\SessionUser($di);
	});

	$di->set('user', function() use ($di) {
		return $di->get('sessionUser')->getUser();
	});

	$di->set('sessionOrder', function() use ($di) {
		return new \order\SessionOrder($di);
	});

	$di->set('order', function() use ($di) {
		return $di->get('sessionOrder')->getOrder();
	});

	$di->set('session', function() {
		$session = new Phalcon\Session\Adapter\Files();
		if (!session_id())
		{
			@$session->start();
		}
		return $session;
	});

	$di->set('flashSession', function(){
		$flash = new \Phalcon\Flash\Session(array(
			'error' => 'alert alert-danger',
			'warning' => 'alert alert-warning',
			'success' => 'alert alert-success',
			'notice' => 'alert alert-info',
		));
		return $flash;
	});

	$di->set(
		'dispatcher',
		function() use ($di) 
		{
			$evManager = $di->getShared('eventsManager');
			$evManager->attach(
				"dispatch:beforeException",
				function($event, $dispatcher, $exception)
				{
					switch ($exception->getCode()) {
						case PhDispatcher::EXCEPTION_HANDLER_NOT_FOUND:
						case PhDispatcher::EXCEPTION_ACTION_NOT_FOUND:
							$dispatcher->forward(
								array(
									'controller' => 'err',
									'action'     => 'index',
								)
							);
							return false;
					}
				}
			);
			$dispatcher = new PhDispatcher();
			$dispatcher->setEventsManager($evManager);
			return $dispatcher;
		},
		true
	);

	$di->set('di', function() use ($di) {
		return function($set) use ($di) {
			foreach ($set as $model)
			{
				$model->setDI($di);
			}

			return $set;
		};
	});

	//Handle the request
	$application = new LiveCart($di);
	//$application->useImplicitView(true);

	// Register the installed modules

	$application->registerModules(
		array(
			'frontend' => function($di) use ($view, $loader) 
			{
				$loader->registerDirs(array(__ROOT__ . 'application/controller'), true);
				//$loader->registerDirs(array(__ROOT__ . 'module/heysuccess/application/controller'), true);
				$loader->registerDirs(array(__ROOT__ . 'module/hybridauth/application/controller'), true);
				
				$di->setShared('view', function() use ($view) {
					$view->setViewsDir(__ROOT__ . 'application/view/');
					return $view;
				});
			},
			'backend' => function($di) use ($view, $loader) 
			{
				$loader->registerDirs(array(__ROOT__ . 'application/controller/backend'), true);
				$di->setShared('view', function() use ($view) {
					$view->setViewsDir(__ROOT__ . 'application/view/backend/');
					return $view;
				});
			},
			/*
			'heysuccess' => function($di) use ($view, $loader) 
			{
				$loader->registerDirs(array(__ROOT__ . 'module/heysuccess/application/controller'), true);
				
				$di->setShared('view', function() use ($view) {
					$view->setViewsDir(__ROOT__ . 'module/heysuccess/application/view/');
					return $view;
				});
			},
			*/
			'hybridauth' => function($di) use ($view, $loader) 
			{
				$loader->registerDirs(array(__ROOT__ . 'module/hybridauth/application/controller'), true);
			}
		)
	);

	$di->set('application', $application);

	echo $application->handle()->getContent();
}
catch (UnauthorizedException $e)
{
	if (!$e->isBackend())
	{
		$di->get('response')->redirect('user/login')->send();
	}
	else
	{
		$di->get('response')->setStatusCode(401, 'Unauthorized')->send();
	}
}
catch (Exception $e)
{
     echo dump_livecart_trace($e);
}

function dump_livecart_trace(Exception $e)
{
	echo "<br/><strong>" . get_class($e) . " ERROR:</strong> " . $e->getMessage()."\n\n";
	echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
	
//	var_dump($e->getMessages());

	echo getFileTrace($e->getTrace());

	//var_dump($e);
	exit;
}

function getFileTrace($trace)
{
	$showedFiles = array();
	$i = 0;
	$traceString = '';

	$ajax = false; //isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;


	// Get new line
	$newLine = $ajax ? "\n" : "<br />\n";

	foreach($trace as $call)
	{
		if(isset($call['file']) && isset($call['line']) && !isset($showedFiles[$call['file']][$call['line']]))
		{
			$showedFiles[$call['file']][$call['line']] = true;

			// Get file name and line
			if($ajax)
			{
				$position = ($i++).": {$call['file']}:{$call['line']}";
			}
			else
			{
				$position = "<strong>".($i++)."</strong>: \"{$call['file']}\":{$call['line']}";
			}

			// Get function name
			if(isset($call['class']) && isset($call['type']) && isset($call['function']))
			{
				$functionName = "{$call['class']}{$call['type']}{$call['function']}";
			}
			else
			{
				$functionName = $call['function'];
			}

			// Get function arguments
			$arguments = '';
			$j = 1;
			if (isset($call['args']))
			{
				foreach($call['args'] as $argv)
				{
					switch(gettype($argv))
					{
						case 'string':
							$arguments .= "\"$argv\"";
						break;
						case 'boolean':
							 $arguments .= ($argv ? 'true' : 'false');
						break;
						case 'integer':
						case 'double':
							 $arguments .= $argv;
						break;
						case 'object':
							 $arguments .= "(object)" . get_class($argv);
						break;
						case 'array':
							 $arguments .= "Array";
						break;
						default:
							$arguments .= $argv;
						break;
					}

					if($j < count($call['args'])) $arguments .= ", "; $j++;
				}
			}


			// format the output line
			$traceString .= "$newLine$position - $functionName($arguments)";
		}
	}

	return $traceString;
}

function get_real_class($inst)
{
	$class = is_string($inst) ? $inst : get_class($inst);
	$parts = explode('\\', $class);
	return array_pop($parts);
}

function persist(\Phalcon\Mvc\Model\Resultset\Simple $set)
{
	$array = array();
	foreach ($set as $item)
	{
		$array[] = $item;
	}
	
	return $array;
}


?>
