<?php

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
		__ROOT__ . 'application/controller/',
		__ROOT__ . 'application/model/',
		__ROOT__ . 'application/',
		__ROOT__ . 'library/',
	))->register();

	//Create a DI
	$di = new Phalcon\DI\FactoryDefault();

	//Setting up the view component
	$di->set('view', function(){
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

		return $view;
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

		var_dump($_REQUEST['_url']);
		//die($base);

		$url->setBaseUri('/livecart2/');

		return $url;
	});

	$di->set('sessionUser', function() use ($di) {
		return new \user\SessionUser($di);
	});

	$di->set('user', function() use ($di) {
		return $di->get('sessionUser')->getUser();
	});

	//Handle the request
	$application = new LiveCart($di);
	//$application->useImplicitView(true);

	$di->set('application', $application);

	echo $application->handle()->getContent();
} catch(Exception $e)
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