<?php
	/**
	 * livecart front controller
	 *
	 * @author Integry Systems
	 * @package application
	 */

// @todo: remove
function onShutDown()
{
	define('SHUTDOWN', true);
}

function logDestruct($obj, $details = '')
{
	if (defined('SHUTDOWN'))
	{
		//echo '! ' . get_class($obj) . ($details ? ' (' . $details . ')' : '') . "\n";
	}
}

register_shutdown_function('onShutDown');


	// session cookie expires in 180 days
	session_set_cookie_params(180 * 60 * 60 * 24);

	include_once (include 'appdir.php') . '/application/Initialize.php';

	ClassLoader::import('application.LiveCart');

	$app = new LiveCart();

	// Custom initialization tasks
	$custom = ClassLoader::getRealPath('storage.configuration.CustomInitialize') . '.php';
	if (file_exists($custom))
	{
		include $custom;
	}

	function runApp(LiveCart $app)
	{
		static $attempts = 0;

		// check if we're not getting into an endless loop
		if (++$attempts > 5)
		{
			try
			{
				$app->run();
			}
			catch (Exception $e)
			{
				echo "<br/><strong>" . get_class($e) . " ERROR:</strong> " . $e->getMessage()."\n\n";
				echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
				echo ApplicationException::getFileTrace($e->getTrace());
				exit;
			}
		}

		try
		{
			$app->run();
		}
		catch (HTTPStatusException $e)
		{
			if($e->getController() instanceof BackendController)
			{
				$route = 'backend.err/redirect/' . $e->getStatusCode();
			}
			else
			{
				$route = 'err/redirect/' . $e->getStatusCode();
			}

			$app->getRouter()->setRequestedRoute($route);
			runApp($app);
		}
		catch (UnsupportedBrowserException $e)
		{
			header('Location: ' . $app->getRouter()->createUrl(array('controller' => 'err', 'action' =>'backendBrowser')));
			exit;
		}
		catch (Exception $e)
		{
			if ($app->isDevMode())
			{
				echo "<br/><strong>" . get_class($e) . " ERROR:</strong> " . $e->getMessage()."\n\n";
				echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
				echo ApplicationException::getFileTrace($e->getTrace());
			}
			else
			{
				$route = 'err/redirect/500';
				$app->getRouter()->setRequestedRoute($route);
				runApp($app);
			}
		}
	}

	runApp($app);

	if (!empty($_GET['stat']))
	{
		$stat->display();
	}

?>
