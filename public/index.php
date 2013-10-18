<?php

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
			$volt = new LiveVolt($view, $di);
			$volt->setOptions(array('compiledPath' => __ROOT__ . 'cache/templates/', 'compileAlways' => true));
			return $volt;
		}
	));

	//Setting up the view component
	$di->set('view', $view);

 	// Specify routes for modules
	$di->set('router', function () {

		$router = new MyRouter();
		$router->setDefaultModule("frontend");

		$router->add("/{handle:[\-a-zA-Z0-9]+}.html", array("controller" => "staticPage", "action" => "view"));
		//$router->add("/{:controller/:action/{id:[0-9]+}", array("controller" => "staticPage", "action" => "view"));
		$router->add("#^/([a-zA-Z0-9\_\-]+)/([a-zA-Z0-9\.\_]+)/([0-9]+)$#", array("controller" => 1, "action" => 2, "id" => 3));

		$router->add('/backend/:controller/:action/:params', array(
			'module' => 'backend',
			'controller' => 1,
			'action' => 2,
			'params' => 3
		));
		

		$router->add('/backend/:controller/:action', array(
			'module' => 'backend',
			'controller' => 1,
			'action' => 2,
		));

		$router->add('/backend/:controller', array(
			'module' => 'backend',
			'controller' => 1
		));

		$router->add('/backend', array(
			'module' => 'backend',
			'controller' => 'index',
			'action' => 'index'
		));

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

	$di->set('session', function() {
		$session = new Phalcon\Session\Adapter\Files();
		$session->start();
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
				$loader->registerDirs(array(__ROOT__ . 'module/mrfeedback/application/controller'), true);
				
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
			'mrfeedback' => function($di) use ($view, $loader) 
			{
				$loader->registerDirs(array(__ROOT__ . 'module/mrfeedback/application/controller'), true);
				
				$di->setShared('view', function() use ($view) {
					$view->setViewsDir(__ROOT__ . 'module/mrfeedback/application/view/');
					return $view;
				});
			}
		)
	);

	$di->set('application', $application);

	echo $application->handle()->getContent();
}
catch(UnauthorizedException $e)
{
	$di->get('response')->redirect('user/login')->send();
}
catch(Exception $e)
{
     echo dump_livecart_trace($e);
}

function dump_livecart_trace(Exception $e)
{
	echo "<br/><strong>" . get_class($e) . " ERROR:</strong> " . $e->getMessage()."\n\n";
	echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
	echo getFileTrace($e->getTrace());
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


?>
