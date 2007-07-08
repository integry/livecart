<?php
	/**
	 * livecart front controller
	 *
	 * @author Integry Systems
	 * @package application
	 */

	// session cookie expires in 180 days
	session_set_cookie_params(180 * 60 * 60 * 24);
    
    include dirname(dirname(__file__)) . '/application/Initialize.php';
    
    ClassLoader::import('application.LiveCart');

	$app = new LiveCart();
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
	    
	    Router::getInstance()->setRequestedRoute($route);
		$app->run();
	}
	catch (ClassLoaderException $e)
	{
		echo "<br/><strong>CLASS LOADER ERROR:</strong> " . $e->getMessage()."\n\n";
		echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
		echo ApplicationException::getFileTrace($e->getTrace());
	}
	catch (ApplicationException $e)
	{
		echo "<pre>"; print_r($_SERVER); echo "</pre>\n\n";
		echo "<br/><strong>APPLICATION ERROR:</strong> " . $e->getMessage()."\n\n";
		echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
		echo ApplicationException::getFileTrace($e->getTrace());
	}
	catch (Exception $e)
	{
		echo "<br/>\n<strong>UNKNOWN ERROR:</strong> " . $e->getMessage()."\n\n";
		// echo "<pre>"; print_r($e); echo "</pre>";
		echo ApplicationException::getFileTrace($e->getTrace());
	}

	if (!empty($_GET['stat']))
	{
		$stat->display();
	}
	
?>