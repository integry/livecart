<?php
	/**
	 * livecart front controller
	 *
	 * @author Integry Systems
	 * @package application
	 */

	// session cookie expires in 180 days
	session_set_cookie_params(180 * 60 * 60 * 24);
    
    include_once dirname(dirname(__file__)) . '/application/Initialize.php';
    
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